<?php
namespace App\Services;

use Carbon\Carbon;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BasicService {
    protected $db;
    protected $fields;
    protected $config;

    public function __construct(string $db)
    {
        $this->db = $db;
        $this->config = [];
    }

    public function count()
    {
        $cmd = ['python3', '../gaipy/SELECT.py', '--database', $this->db];
        $this->config = array_merge($cmd, $this->config);

        $process = new Process($this->config);

        try {
            $process->mustRun();
            $result = json_decode($process->getOutput(), true);
            if ($result['res'] == true) {
                return $result['data']['cnt'];
            } else {
                return -1;
            }
        } catch (ProcessFailedException $exception) {
            dd($exception);
            return -1;
        }
    }

    public function select()
    {
        $json_builder = $this->fields;

        $col_list = [];
        $val_list = [];
        foreach ($this->fields as $key => $value) {
            array_push($col_list, $key);
            array_push($val_list, $value);
        }

        $p_list = ['col' => $col_list, 'val' => $val_list];

    }

    public function pattern(array $fields = [])
    {
        $col_list = [];
        $val_list = [];
        foreach ($fields as $key => $value) {
            array_push($col_list, $key);
            array_push($val_list, $value);
        }
        $p_list = ['col' => $col_list, 'val' => $val_list];
        array_push($this->config, '--pattern');
        array_push($this->config, json_encode($p_list));
        return $this;
    }

    public function filter(array $fields = [])
    {
        $col_list = [];
        $val_list = [];
        foreach ($fields as $key => $value) {
            array_push($col_list, $key);
            array_push($val_list, $value);
        }
        $p_list = ['col' => $col_list, 'val' => $val_list];
        array_push($this->config, '--filter-args');
        array_push($this->config, json_encode($p_list));
        return $this;
    }

    public function pageCount(int $page_count = 10)
    {
        array_push($this->config, '--page-count');
        array_push($this->config, $page_count);
        return $this;
    }

    public function page(int $page_number = 1)
    {
        array_push($this->config, '--page-number');
        array_push($this->config, $page_number);
        return $this;
    }

    public function orderBy(string $col = 'rid', string $order_attr = 'desc')
    {
        array_push($this->config, '--order-by');
        array_push($this->config, $col);
        $order_attr = mb_strtolower($order_attr);
        array_push($this->config, '--order-attr');
        array_push($this->config, $order_attr);
        return $this;
    }

    public function get()
    {
        $cmd = ['python3', '../gaipy/SELECT.py', '--database', $this->db];
        $this->config = array_merge($cmd, $this->config);
        $process = new Process($this->config);
        try {
            $process->mustRun();
            $result = json_decode($process->getOutput());
            return $result;
        } catch (ProcessFailedException $exception) {
            dd($exception);
            return -1;
        }
    }

    public function save(array $fields = [], int $rid = 0)
    {
        $record_field = ['--record-arg', json_encode($fields)];
        if (count($this->config) != 0) { // select-update mode
            // pick up all the records
            $cmd = ['python3', '../gaipy/SELECT.py', '--database', $this->db];
            $select_cmd = array_merge($cmd, $this->config);
            $process = new Process($select_cmd);
            try {
                $process->mustRun();
                $result = json_decode($process->getOutput(), true);
            } catch (ProcessFailedException $exception) {
                $result = null;
            }
            $success_cnt = 0;
            foreach ($result['data']['recs'] as $key => $value) {
                $cmd = ['python3', '../gaipy/UPDATE.py', '--database', $this->db];
                $update_cmd = array_merge($cmd, ['--record-id', $value['rid']]);
                $update_cmd = array_merge($update_cmd, $record_field);
                $process = new Process($update_cmd);
                try {
                    $process->mustRun();
                    $result = json_decode($process->getOutput(), true);
                    if ($result['res'] == true) {
                        $success_cnt++;
                    }
                } catch (ProcessFailedException $exception) {
                    return ['res' => False, 'success_cnt' => 0];
                }
            }
            return ['res' => True, 'success_cnt' => $success_cnt];
        } elseif ($rid != 0) {
            $success_cnt = 0;
            $cmd = ['python3', '../gaipy/UPDATE.py', '--database', $this->db];
            $update_cmd = array_merge($cmd, ['--record-id', $rid]);
            $update_cmd = array_merge($update_cmd, $record_field);
            $process = new Process($update_cmd);
            try {
                $process->mustRun();
                $result = json_decode($process->getOutput(), true);
                if ($result['res'] == true) {
                    $success_cnt++;
                }
            } catch (ProcessFailedException $exception) {
                return ['res' => False, 'success_cnt' => 0];
            }
            return ['res' => True, 'success_cnt' => $success_cnt];
        } else { // insert mode
            $cmd = ['python3', '../gaipy/INSERT.py', '--database', $this->db];
            $insert_cmd = array_merge($cmd, $this->config);
            $insert_cmd = array_merge($insert_cmd, $record_field);
            $process = new Process($insert_cmd);
            try {
                $process->mustRun();
                $result = json_decode($process->getOutput(), true);
            } catch (ProcessFailedException $exception) {
                $result = ['res' => False];
            }
            return $result;
        }
    }

    public function delete(int $rid = 0)
    {
        if (count($this->config) != 0) { // select-update mode
            // pick up all the records
            $cmd = ['python3', '../gaipy/SELECT.py', '--database', $this->db];
            $select_cmd = array_merge($cmd, $this->config);
            $process = new Process($select_cmd);
            try {
                $process->mustRun();
                $result = json_decode($process->getOutput(), true);
            } catch (ProcessFailedException $exception) {
                $result = null;
            }
            $rid_list = [];
            foreach ($result['data']['recs'] as $key => $value) {
                array_push($rid_list, $value['rid']);
            }
            $cmd = ['python3', '../gaipy/DELETE.py', '--database', $this->db];
            $delete_cmd = array_merge($cmd, ['--record-id', json_encode($rid_list)]);
        } elseif ($rid != 0) {
            $cmd = ['python3', '../gaipy/DELETE.py', '--database', $this->db];
            $delete_cmd = array_merge($cmd, ['--record-id', json_encode([$rid])]);
        } else {
            return ['res' => False, 'success_cnt' => 0];
        }
        $process = new Process($delete_cmd);
        try {
            $process->mustRun();
            $result = json_decode($process->getOutput(), true);
        } catch (ProcessFailedException $exception) {
            return ['res' => False, 'success_cnt' => 0];
        }
        return $result;
    }

}
