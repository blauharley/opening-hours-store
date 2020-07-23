<?php

date_default_timezone_set('Europe/Vienna');

require_once('helpers/timeEntries/OpeningHourTimeEntry.php');
require_once('helpers/timeEntries/StateExceptionTimeEntry.php');
require_once('helpers/DaysOfWeek.php');
require_once('helpers/Timespan.php');
require_once('layers/Store.php');
require_once('layers/ChargingStation.php');
require_once('layers/tenants/Customer.php');
require_once('layers/tenants/Employee.php');

use entities\OpeningHourTimeEntry;
use entities\DaysOfWeek;
use entities\Timespan;
use entities\Store;
use entities\ChargingStation;
use entities\tenants\Customer;
use entities\tenants\Employee;
use exceptions\StateExceptionTimeEntry;
use helpers\StateException;

# layer 1
# assuming that those AbstractTenant instances have authenticated successfully
$authenticatedCustomer = new Customer(
    'Customer A',
    [
        new StateExceptionTimeEntry(
            2020,
            12,
            24,
            new StateException(false, 'Public christmas'),
            [new Timespan(0, 0, 23, 59)]
        ),
        new StateExceptionTimeEntry(
            2020,
            12,
            25,
            new StateException(false, 'Public christmas'),
            [new Timespan(0, 0, 23, 59)]
        ),
        new StateExceptionTimeEntry(
            2020,
            12,
            26,
            new StateException(false, 'Public christmas'),
            [new Timespan(0, 0, 23, 59)]
        )
    ]
);
$authenticatedEmployee = new Employee(
    'Employee B',
    [
        new StateExceptionTimeEntry(
            2020,
            5,
            1,
            new StateException(false, 'Public holiday'),
            [new Timespan(0, 0, 23, 59)]
        ),
        new StateExceptionTimeEntry(
            2020,
            5,
            2,
            new StateException(false, 'Public holiday'),
            [new Timespan(0, 0, 23, 59)]
        )
    ]
);

# layer 2
$customerCharStat = new ChargingStation(
    $authenticatedCustomer,
    [
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Monday,
                DaysOfWeek::Tuesday,
                DaysOfWeek::Thursday
            ],
            [
                new Timespan(8, 24, 12, 0),
                new Timespan(13, 0, 17, 30)
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Wednesday
            ],
            [
                new Timespan(8, 15, 13, 0),
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Friday
            ],
            [
                new Timespan(8, 0, 12, 0),
                new Timespan(13, 0, 20, 0),
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Saturday
            ],
            [
                new Timespan(10, 0, 13, 0)
            ]
        )
    ],
    [
        new StateExceptionTimeEntry(
            2020,
            7,
            20,
            new StateException(false, 'Planned maintenance'),
            [new Timespan(9, 0, 11, 0)]
        )
    ]
);
$employeeCharStat = new ChargingStation(
    $authenticatedEmployee,
    [
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Monday,
                DaysOfWeek::Tuesday,
                DaysOfWeek::Thursday
            ],
            [
                new Timespan(6, 30, 19, 0),
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Wednesday,
            ],
            [
                new Timespan(6, 45, 14, 30),
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Friday
            ],
            [
                new Timespan(6, 55, 21, 0),
            ]
        ),
        new OpeningHourTimeEntry(
            [
                DaysOfWeek::Saturday
            ],
            [
                new Timespan(9, 0, 14, 30)
            ]
        )
    ],
    [
        new StateExceptionTimeEntry(
            2020,
            8,
            14,
            new StateException(true, 'Promotional event'),
            [new Timespan(8, 0, 20, 0)]
        )
    ]
);

# layer 3
$store = new Store(
    [
        $customerCharStat,
        $employeeCharStat
    ],
    [
        new StateExceptionTimeEntry(
            2020,
            9,
            19,
            new StateException(false, 'Construction at store'),
            [new Timespan(8, 0, 20, 0)]
        ),
        new StateExceptionTimeEntry(
            2020,
            9,
            20,
            new StateException(false, 'Construction at store'),
            [new Timespan(8, 0, 20, 0)]
        )
    ]
);

list($file, $kindOfRequest, $givenTime, $tenant) = $argv;

$answer = null;
$givenTime = strtotime($givenTime);
$stations = $tenant === 'c' ? $store->getStationsForTenant(Customer::class) : $store->getStationsForTenant(Employee::class);

# assuming that it's always the first station
/** @var ChargingStation $station **/
$station = array_pop($stations);

try{
    if ($kindOfRequest === 'status') {
        // relevant method 1
        $answer = $station->isOpenAt($givenTime) ? 'yes' : 'no';
    } elseif ($kindOfRequest === 'next') {
        // relevant method 2
        $nextTimestamp = $station->getTimestampOfNextStateChangeStartingAt($givenTime);
        $answer = new DateTime();
        $answer = $answer->setTimestamp($nextTimestamp)->format('Y-m-d H:i');
    }
} catch (Exception $exception) {
    $answer = $exception->getMessage();
}

echo 'AbstractTenant-ID: ' . $station->getTenant()->getId() . ' #/# Responds : ' . $answer . "\n";
