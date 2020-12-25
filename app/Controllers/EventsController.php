<?php

namespace App\Controllers;

use App\Models\Events;
use Exception;

class EventsController extends Controller
{
    public function all()
    {
        try {
            $events = Events::orderBy('start_date');
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }

        self::send($events);
    }

    public function find(int $id)
    {
        try {
            self::send(Events::find($id));
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    public function delete(int $id)
    {
        $this->watchdog();

        try {
            self::send(Events::delete($id));
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    public function add()
    {
        $this->watchdog();

        $validData = self::validate($_POST, [
            'title' => 'required',
            'info' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        try {
            self::send(Events::create($validData), 201);
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }

    public function edit(int $id)
    {
        $this->watchdog();

        $_PATCH = self::readData();

        $validData = self::validate($_PATCH, [
            'title' => 'optional',
            'info' => 'optional',
            'start_date' => 'optional',
            'end_date' => 'optional'
        ]);

        try {
            self::send(Events::update($id, $validData));
        } catch (Exception $e) {
            self::error($e->getMessage(), $e->getCode());
        }
    }
}
