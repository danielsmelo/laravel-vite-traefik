<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function welcome(Request $request)
    {
        return view('welcome')->with('result', [])->with('users', $this->data($request));
    }

    public function data(Request $request)
    {
        $page = $request->input('page', 1);

        $perPage = 10;
        $array = array();
        $skip = ($page - 1) * $perPage;
        $data = User::skip($skip)->take($perPage)->get();

        $array = $data->toArray();

        return json_encode($array);
    }

    public function simulate(Request $request)
    {

        return [
            'success' => true,
            'result' => $this->data($request),
            'pageverify' => $request->input('page', 1)

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
