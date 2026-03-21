<?php

class agclienteworkerModuleFrontController extends ModuleFrontController
{
    /** @var AgClienteWorker */
    protected $group;
    public function initContent()
    {
        if (Tools::getValue('action') == 'removeOld') {
            Db::getInstance()->delete('agworker', 'date_upd < "' . date('Y-m-d H:i:s', strtotime('-3 days')) . '"');
            exit();
        }

        if (time() - Configuration::get('AGCLIENTE_WORKER_WATCHDOG') <= 120 && !Tools::getValue('force')) {
            exit();
        }

        $group = AgClienteWorkerGroup::findByName('agcliente_main');
        $asso = AgClienteWorkerGroupShop::getFromWorkerGroup($group);

        $this->group = $asso;
        $this->group->key_for_workers = uniqid();

        $this->group->save();

        while(1) {
            Configuration::updateValue('AGCLIENTE_WORKER_WATCHDOG', time());
            $this->doLoop();
            $this->checkShouldDie();
            sleep(15);
        }

        exit();
        
    }

    protected function isWorkerExecutable($time_from, $time_to) {
        $current_time = time(); // Obtém o timestamp atual
    
        if (!$time_from && !$time_to) {
            // Se ambos time_from e time_to forem NULL, a worker pode ser executada a qualquer momento.
            return true;
        } elseif ($time_from && $time_to) {
            // Se tanto time_from quanto time_to forem especificados, verifique se o tempo atual está dentro do intervalo.
            $time_from_timestamp = strtotime($time_from);
            $time_to_timestamp = strtotime($time_to);
    
            if ($time_from_timestamp > $time_to_timestamp) {
                // Se time_from for maior do que time_to, o intervalo atravessa a meia-noite.
                return ($current_time >= $time_from_timestamp || $current_time <= $time_to_timestamp);
            } else {
                return ($current_time >= $time_from_timestamp && $current_time <= $time_to_timestamp);
            }
        } elseif ($time_from) {
            // Se apenas time_from for especificado, verifique se o tempo atual é posterior ou igual a time_from.
            $time_from_timestamp = strtotime($time_from);
            return ($current_time >= $time_from_timestamp);
        } elseif ($time_to) {
            // Se apenas time_to for especificado, verifique se o tempo atual é anterior ou igual a time_to.
            $time_to_timestamp = strtotime($time_to);
            return ($current_time <= $time_to_timestamp);
        }
    
        return false; // Caso contrário, a worker não é executável.
    }
    

    protected function doLoop()
    {
        $groups = AgClienteWorkerGroup::getAll();

        foreach ($groups as $group) {
            if ($group->group_name == 'agcliente_main') {
                continue;
            }

            
            if(!$this->isWorkerExecutable($group->time_from, $group->time_to)){
                $group->killWorkers();
                continue;
            }
            
            
            //verifica se há a quantidade certa de workers em execução
            $workers = AgClienteWorker::findByGroup($group);

            //todas as workers esperadas estão em execução
            if (count($workers) >= $group->qty_wanted_workers) {
                continue;
            }

            AgClienteLogger::addLog("Worker {$group->group_name} possui " . count($workers) . " workers em execução. Deveria ter " . $group->qty_wanted_workers .".", 2, null, null, null, true);
            $group->killWorkers();
            $group->createWorkers();
        }
    }

    protected function checkShouldDie()
    {
        $group = AgClienteWorkerGroup::findByName('agcliente_main');
        $asso = AgClienteWorkerGroupShop::getFromWorkerGroup($group);

        if ($asso->key_for_workers != $this->group->key_for_workers) {
            exit();
        }
    }
}