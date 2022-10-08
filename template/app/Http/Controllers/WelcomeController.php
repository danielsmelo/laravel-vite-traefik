<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function welcome(Request $request)
    {
        return view('welcome')->with('result', []);
    }

    public function simulate(Request $request)
    {
        $data = $request->all();

        $cliente = 0;
        $relogio = 0;
        $evento = 'Início';
        $fila = array();
        $operador = null;
        $TC = 0;
        $calendario[] = [
            'client' => '-',
            'globalTime' => floatval($data['quantity']),
            'event' => 'FS'
        ];
        $calendario[] = $this->newEventArrival(1, $relogio);
        $contaCliente = 0;
        $sumTF = 0;
        $maxTF = 0;
        $sumTS = 0;
        $maxTS = 0;

        usort($calendario, function ($item1, $item2) {
            if ($item1['globalTime'] == $item2['globalTime']) {
                return 0;
            }
            return $item1['globalTime'] < $item2['globalTime'] ? -1 : 1;
        });

        $result[] = [
            'client' => $cliente,
            'globalTime' => $relogio,
            'event' => 'Início',
            'queueState' => count($fila) > 0 ? count($fila) : '-',
            'employeeState' => $operador != null ? $operador : 'Livre',
            'TC' => $TC,
            'eventCalendar' => $this->calendarToString($calendario),
            'clientCount' => $contaCliente,
            'sumTF' => $sumTF,
            'maxTF' => $maxTF,
            'sumTS' => $sumTS,
            'maxTS' => $maxTS,
        ];

        while ($calendario[0]['event'] != 'FS') {
            $cliente = $calendario[0]['client'];
            $evento = $calendario[0]['event'];

            if ($evento == 'C' && count($fila) == 0 && $operador == null) {

                $TC = $relogio + $calendario[0]['timeBetweenArrival'];
                $relogio = $calendario[0]['globalTime'];
                $operador = $cliente;

                $calendarioAux = $calendario;
                array_shift($calendario);

                $newLeaveEvent = $this->getLeaveEvent($cliente, $data, $TC);
                $calendario[] = $newLeaveEvent;
                $calendario[] = $this->newEventArrival($cliente + 1, $TC);

                $calendario = $this->sortCalendar($calendario);

                $result[] = [
                    'client' => $cliente,
                    'globalTime' => $relogio,
                    'event' => $evento == 'C' ? 'Chegada' : 'Saída',
                    'queueState' => count($fila) > 0 ? count($fila) : '-',
                    'employeeState' => $operador != null ? $operador : 'Livre',
                    'TC' => $TC,
                    'eventCalendar' => $this->calendarToString($calendario),
                    'clientCount' => $contaCliente,
                    'sumTF' => $sumTF,
                    'maxTF' => $maxTF,
                    'sumTS' => $sumTS,
                    'maxTS' => $maxTS,
                ];
            } else if ($evento == 'C' && $operador != null) {

                $TC = $relogio + $calendario[0]['timeBetweenArrival'];
                $relogio = $calendario[0]['globalTime'];

                $fila[] = array_shift($calendario);

                $calendario[] = $this->newEventArrival($cliente + 1, $TC);

                $calendario = $this->sortCalendar($calendario);

                $result[] = [
                    'client' => $cliente,
                    'globalTime' => $relogio,
                    'event' => $evento == 'C' ? 'Chegada' : 'Saída',
                    'queueState' => count($fila) > 0 ? count($fila) : '-',
                    'employeeState' => $operador != null ? $operador : 'Livre',
                    'TC' => $TC,
                    'eventCalendar' => $this->calendarToString($calendario),
                    'clientCount' => $contaCliente,
                    'sumTF' => $sumTF,
                    'maxTF' => $maxTF,
                    'sumTS' => $sumTS,
                    'maxTS' => $maxTS,
                ];
            } else if ($evento == 'S' && count($fila) != 0 && $operador != null) {

                $contaCliente++;

                $operador = array_shift($fila)['client'];

                $eventOut = array_shift($calendario);

                $relogio = $eventOut['globalTime'];

                $calendario[] = $this->getLeaveEvent($operador, $data, $relogio);

                $this->sortCalendar($calendario);

                $sumTF += $relogio - $TC;

                $maxTF = max($maxTF, $relogio - $TC);

                $sumTS += $eventOut['timeOfService'] + ($relogio - $TC);

                $maxTS = max($maxTS, ($eventOut['timeOfService'] + ($relogio - $TC)));

                $calendario = $this->sortCalendar($calendario);

                $result[] = [
                    'client' => $cliente,
                    'globalTime' => $relogio,
                    'event' => $evento == 'C' ? 'Chegada' : 'Saída',
                    'queueState' => count($fila) > 0 ? count($fila) : '-',
                    'employeeState' => $operador != null ? $operador : 'Livre',
                    'TC' => '-',
                    'eventCalendar' => $this->calendarToString($calendario),
                    'clientCount' => $contaCliente,
                    'sumTF' => $sumTF,
                    'maxTF' => $maxTF,
                    'sumTS' => $sumTS,
                    'maxTS' => $maxTS,
                ];
            } else if ($evento == 'S' && count($fila) == 0) {

                $contaCliente++;

                $operador = null;

                $relogio = $calendario[0]['globalTime'];

                $eventOut = array_shift($calendario);

                $sumTF += $relogio - $TC;

                $maxTF = max($maxTF, $relogio - $TC);

                $sumTS += $eventOut['timeOfService'] + ($relogio - $TC);

                $maxTS = max($maxTS, ($eventOut['timeOfService'] + ($relogio - $TC)));

                $calendario = $this->sortCalendar($calendario);

                $result[] = [
                    'client' => $cliente,
                    'globalTime' => $relogio,
                    'event' => $evento == 'C' ? 'Chegada' : 'Saída',
                    'queueState' => count($fila) > 0 ? count($fila) : '-',
                    'employeeState' => $operador != null ? $operador : 'Livre',
                    'TC' => '-',
                    'eventCalendar' => $this->calendarToString($calendario),
                    'clientCount' => $contaCliente,
                    'sumTF' => $sumTF,
                    'maxTF' => $maxTF,
                    'sumTS' => $sumTS,
                    'maxTS' => $maxTS,
                ];
            }

            $calendario = $this->sortCalendar($calendario);
        }

        $statistics = $this->getStatistics($result);

        return [
            'success' => true,
            'result' => $result,
            'statistics' => $statistics,
        ];
    }

    public function sortCalendar($calendario)
    {
        usort($calendario, function ($item1, $item2) {
            if ($item1['globalTime'] == $item2['globalTime']) {
                return 0;
            }
            return $item1['globalTime'] < $item2['globalTime'] ? -1 : 1;
        });

        return $calendario;
    }

    public function calendarToString($calendario)
    {
        $calendarioString = '';
        foreach ($calendario as $key => $value) {
            $calendarioString .= '{' . $value['client'] . '; ' . $value['globalTime'] . '; ' . $value['event'] . '} <br>';
        }

        return $calendarioString;
    }

    public function newEventArrival($client, $relogio)
    {
        $timeBetweenArrival = floatval($this->getClassMidpoint());

        $event = [
            'client' => $client,
            'timeBetweenArrival' => $timeBetweenArrival,
            'globalTime' => $relogio + $timeBetweenArrival,
            'event' => 'C'
        ];

        return $event;
    }

    public function getLeaveEvent($client, $data, $relogio)
    {
        $timeOfService = floatval($this->getServiceTime($data));

        return [
            'client' => $client,
            'timeOfService' => $timeOfService,
            'globalTime' => $relogio + $timeOfService,
            'event' => 'S'
        ];
    }

    public function getClassMidpoint()
    {
        $random = mt_rand()  /  mt_getrandmax(); //Mersenne Twister Algorithm

        $random = floatval(number_format((float)$random, 2, '.', ''));

        switch ($random) {
            case $random >= 0.00 && $random <= 0.35:
                return 2.5;
                break;
            case $random >= 0.36 && $random <= 0.54:
                return 7.5;
                break;
            case $random >= 0.55 && $random <= 0.73:
                return 12.5;
                break;
            case $random >= 0.74 && $random <= 0.86:
                return 17.5;
                break;
            case $random >= 0.87 && $random <= 0.89:
                return 22.5;
                break;
            case $random >= 0.90 && $random <= 0.96:
                return 27.5;
                break;
            case $random == 0.97:
                return 32.5;
                break;
            case $random >= 0.98 && $random <= 0.99:
                return 37.5;
                break;
            case $random == 1:
                return 42.5;
                break;
        }
    }

    public function getServiceTime($data)
    {
        $random = mt_rand()  /  mt_getrandmax(); //Mersenne Twister Algorithm

        $random = floatval(number_format((float)$random, 2, '.', ''));

        switch ($random) {
            case $random >= 0.00 && $random <= 0.35:
                return $data['tsAverage'];
                break;
            case $random >= 0.36 && $random <= 0.64:
                return $data['tsAverage'] + $data['tsVariation'];
                break;
            case $random >= 0.64 && $random <= 1:
                return $data['tsAverage'] - $data['tsVariation'];
                break;
            default:
                return $data['tsAverage'];
        }
    }

    public function getStatistics($result)
    {

        $totalClients = end($result)['clientCount'];

        $queueClients = 0;

        foreach ($result as $key => $value) {
            $test = $value['queueState'];
            $test2 = $value['event'];

            if ($value['event'] == 'Chegada' && $value['queueState'] != '-') {
                $queueClients++;
            }
        }

        $averageTimeQueue = end($result)['sumTF'] / $totalClients;

        $queueProbability = $queueClients / $totalClients;

        $employeeFreeTime = 0;

        $event = null;
        $nextEvent = null;
        $resultAux = $result;

        while ($resultAux) {
            if ($nextEvent != null && $nextEvent['employeeState'] == 'Livre') {
                $nextEvent = array_shift($resultAux);

                continue;
            } elseif ($nextEvent != null && $nextEvent['employeeState'] != 'Livre') {
                $employeeFreeTime += $nextEvent['globalTime'] - $event['globalTime'];
                $nextEvent = null;
            }

            $event = array_shift($resultAux);

            $employeeState = $event['employeeState'];

            if ($event['employeeState'] == 'Livre') {
                $nextEvent = array_shift($resultAux);
            } else {
                continue;
            }
        }

        $employeeFreeTimeProportion = $employeeFreeTime / end($result)['globalTime'];

        $employeeBusyTime = 1 - $employeeFreeTimeProportion;

        $averageTimeo0nSystem = end($result)['sumTS'] / $totalClients;

        return [
            'totalClients' => floatval(number_format((float)$totalClients, 2, '.', '')),
            'queueClients' => floatval(number_format((float)$queueClients, 2, '.', '')),
            'averageTimeQueue' => floatval(number_format((float)$averageTimeQueue, 2, '.', '')),
            'queueProbability' => floatval(number_format((float)$queueProbability, 2, '.', '')),
            'employeeFreeTime' => floatval(number_format((float)$employeeFreeTime, 2, '.', '')),
            'employeeFreeTimeProportion' => floatval(number_format((float)$employeeFreeTimeProportion, 2, '.', '')),
            'employeeBusyTime' => floatval(number_format((float)$employeeBusyTime, 2, '.', '')),
            'averageTimeo0nSystem' => floatval(number_format((float)$averageTimeo0nSystem, 2, '.', '')),
        ];
    }
}
