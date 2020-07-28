This is a standalone PHP-project that can handle multiple charging-stations of different stores along with different tenants. 
The `ChargingStation` has got two methods that rely on opening-hours (`OpeningHourTimeEntry`) and exceptions (`StateExceptionTimeEntry`).
Those methods have following signature:

* `isOpenAt(int $requestedTimestamp): bool`: It returns `true` or `false` indicating whether the `ChargingStation` is open.
What it returns depends on the `requestedTimestamp` and what `OpeningHourTimeEntry`'s and `StateExceptionTimeEntry`'s are assigned to this `ChargingStation`. 

* `getTimestampOfNextStateChangeStartingAt(int $requestedTimestamp): int`: It returns a timestamp indicating when
the `ChargingStation` changes it's open|closed-state again. 
What it returns depends on the `requestedTimestamp` and what `OpeningHourTimeEntry`'s and `StateExceptionTimeEntry`'s are assigned to this `ChargingStation`. 

# `main.php` Programm arguments-order:

1. `status`: It calls `isOpenAt`.
2. `YYYY-MM-DD HH:mm`: Human readbale Date representation.
3. `c` or `e`: Tenant-Switching between a Customer (`c`) and an Employee (`e`)

# Examples

* `php main.php status '2020-12-31 12:34' c` -> returns `false` because 
`new Timespan(8, 24, 12, 0)` on `DaysOfWeek::Thursday` ends at 12 o'clock.

* `php main.php next '2020-12-31 19:34' c` -> returns `1609484400` (`2021-01-01 08:00`) because 
`[new Timespan(8, 24, 12, 0), new Timespan(13, 0, 17, 30)]` closed at 17:30 o'clock, but will open at 8 o'clock on the next day again.

`StateExceptionTimeEntry` can be used to define special `Timespan`'s that can have an effect on different layers (``ChargingStation`, `Store` and `Tenant`) in both directions.

* `php main.php status '2020-12-24 11:45' c` -> returns `false`, although it's also a `DaysOfWeek::Thursday`.

For more info, please have a look into `main.php`.

# PHP-Version: 7.4.8
