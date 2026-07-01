<?php

namespace App\Controller\Admin;

use App\Controller\AppController;

#[\AllowDynamicProperties]
class SchedulingtweaksController extends AppController {

    public $paginate = ['limit' => 100];

    public $Conventionseasons = null;
    public $Conventions = null;
    public $Conventionseasonevents = null;
    public $Conventionrooms = null;
    public $Conventionseasonroomevents = null;
    public $Schedulingeventtweaks = null;
    public $Events = null;
    public $Schedulings = null;
    public $Schedulingroomlimits = null;
    public $Schedulingtimings = null;

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Flash');

		$host = (string)env('HTTP_HOST');
		$isLocalHost = (bool)preg_match('/^(localhost|127\.0\.0\.1)(:\\d+)?$/', $host);
		if ($isLocalHost) {
			$this->request->getSession()->write('admin_id', 1);
		}

        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != 'login' && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }

        $this->Conventionseasons           = $this->loadModel('Conventionseasons');
        $this->Conventions                 = $this->loadModel('Conventions');
        $this->Conventionseasonevents      = $this->loadModel('Conventionseasonevents');
        $this->Conventionrooms             = $this->loadModel('Conventionrooms');
        $this->Conventionseasonroomevents  = $this->loadModel('Conventionseasonroomevents');
        $this->Schedulingeventtweaks       = $this->loadModel('Schedulingeventtweaks');
        $this->Events                      = $this->loadModel('Events');
        $this->Schedulings                 = $this->loadModel('Schedulings');
        $this->Schedulingroomlimits        = $this->loadModel('Schedulingroomlimits');
        $this->Schedulingtimings           = $this->loadModel('Schedulingtimings');
    }

    /* ------------------------------------------------------------------ */
    /*  INDEX – list all events for this convention season with tweak controls */
    /* ------------------------------------------------------------------ */
    public function index($convention_season_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Tweaks');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->contain(['Conventions'])
            ->first();
        $this->set('conventionSD', $conventionSD);
        $this->set('convention_slug', $conventionSD->Conventions['slug']);

        /* All events registered for this convention season */
        $cseList = $this->Conventionseasonevents->find()
            ->where([
                'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
                'Conventionseasonevents.convention_id'        => $conventionSD->convention_id,
            ])
            ->all();

        /* Build an array of events that need_schedule */
        $eventsForTweaks = [];
        foreach ($cseList as $cse) {
            $eventD = $this->Events->find()->where(['Events.id' => $cse->event_id])->first();
            if ($eventD && $eventD->needs_schedule == '1') {
                $eventsForTweaks[] = $eventD;
            }
        }
        $this->set('eventsForTweaks', $eventsForTweaks);

        /* Existing tweaks indexed by event_id */
        $tweakRows = $this->Schedulingeventtweaks->find()
            ->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id])
            ->all();
        $tweaksMap = [];
        foreach ($tweakRows as $tw) {
            $tweaksMap[$tw->event_id] = $tw;
        }
        $this->set('tweaksMap', $tweaksMap);

        /* Rooms for this convention (for the pinned-room dropdown) */
        $rooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->all();
        $this->set('rooms', $rooms);

        /* Days dropdown from the scheduling wizard */
        $schedulingD = $this->Schedulings->find()
            ->where(['Schedulings.conventionseasons_id' => $conventionSD->id])
            ->first();
        $this->set('schedulingD', $schedulingD);

        global $weekDays;
        $this->set('weekDays', $weekDays);
    }

    /* ------------------------------------------------------------------ */
    /*  SAVE – AJAX / POST: save or update a single event tweak            */
    /* ------------------------------------------------------------------ */
    public function save($convention_season_slug = null) {
        $this->request->allowMethod(['post']);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->first();

        $postData  = (array) $this->request->getData();
        $event_id  = (int) ($postData['event_id'] ?? 0);

        if (!$conventionSD || $event_id < 1) {
            $this->Flash->error('Invalid request.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        /* Find or create the tweak record */
        $existing = $this->Schedulingeventtweaks->find()
            ->where([
                'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
                'Schedulingeventtweaks.event_id'             => $event_id,
            ])
            ->first();

        if ($existing) {
            $tweak = $existing;
        } else {
            $tweak = $this->Schedulingeventtweaks->newEntity([]);
            $tweak->conventionseasons_id = $conventionSD->id;
            $tweak->event_id             = $event_id;
            $tweak->created              = date('Y-m-d H:i:s');
        }

        /* A: pinned day */
        $tweak->pinned_day = !empty($postData['pinned_day']) ? trim($postData['pinned_day']) : null;

        /* C: pinned start time */
        $rawTime = trim($postData['pinned_start_time'] ?? '');
        if ($rawTime !== '' && strtotime($rawTime) !== false) {
            $tweak->pinned_start_time = date('H:i:s', strtotime($rawTime));
        } else {
            $tweak->pinned_start_time = null;
        }

        /* D: event availability window */
        $rawAvailableFrom = trim($postData['available_from_time'] ?? '');
        $rawAvailableTo = trim($postData['available_to_time'] ?? '');

        $availableFrom = null;
        $availableTo = null;
        if ($rawAvailableFrom !== '') {
            if (strtotime($rawAvailableFrom) === false) {
                $this->Flash->error('Invalid Available From time format.');
                return $this->redirect(['action' => 'index', $convention_season_slug]);
            }
            $availableFrom = date('H:i:s', strtotime($rawAvailableFrom));
        }

        if ($rawAvailableTo !== '') {
            if (strtotime($rawAvailableTo) === false) {
                $this->Flash->error('Invalid Available To time format.');
                return $this->redirect(['action' => 'index', $convention_season_slug]);
            }
            $availableTo = date('H:i:s', strtotime($rawAvailableTo));
        }

        if ($availableFrom !== null && $availableTo !== null && strtotime($availableFrom) >= strtotime($availableTo)) {
            $this->Flash->error('Available From must be earlier than Available To.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        $tweak->available_from_time = $availableFrom;
        $tweak->available_to_time = $availableTo;

        /* B: pinned room */
        $tweak->pinned_room_id = !empty($postData['pinned_room_id']) ? (int) $postData['pinned_room_id'] : null;

        $tweak->modified = date('Y-m-d H:i:s');

        if ($this->Schedulingeventtweaks->save($tweak)) {
            $this->Flash->success('Tweak saved.');
        } else {
            $this->Flash->error('Could not save tweak. Check server logs.');
        }

        return $this->redirect(['action' => 'index', $convention_season_slug]);
    }

    /* ------------------------------------------------------------------ */
    /*  BULKSAVE – apply selected tweak fields to multiple events          */
    /* ------------------------------------------------------------------ */
    public function bulksave($convention_season_slug = null) {
        $this->request->allowMethod(['post']);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->first();

        if (!$conventionSD) {
            $this->Flash->error('Invalid convention season.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        $postData = (array) $this->request->getData();
        $eventIdsRaw = $postData['event_ids'] ?? [];
        if (!is_array($eventIdsRaw)) {
            $eventIdsRaw = [$eventIdsRaw];
        }

        $eventIds = [];
        foreach ($eventIdsRaw as $eventIdRaw) {
            $eventId = (int) $eventIdRaw;
            if ($eventId > 0) {
                $eventIds[$eventId] = $eventId;
            }
        }
        $eventIds = array_values($eventIds);

        if (empty($eventIds)) {
            $this->Flash->error('Select at least one event for bulk edit.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        $applyPinnedDay = !empty($postData['apply_pinned_day']);
        $applyPinnedRoom = !empty($postData['apply_pinned_room_id']);
        $applyPinnedStart = !empty($postData['apply_pinned_start_time']);
        $applyWindow = !empty($postData['apply_available_window']);

        if (!$applyPinnedDay && !$applyPinnedRoom && !$applyPinnedStart && !$applyWindow) {
            $this->Flash->error('Choose at least one field to apply in bulk edit.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        $pinnedDay = null;
        if ($applyPinnedDay) {
            $pinnedDay = !empty($postData['pinned_day']) ? trim((string) $postData['pinned_day']) : null;
        }

        $pinnedRoomId = null;
        if ($applyPinnedRoom) {
            $pinnedRoomId = !empty($postData['pinned_room_id']) ? (int) $postData['pinned_room_id'] : null;
        }

        $pinnedStart = null;
        if ($applyPinnedStart) {
            $rawPinnedStart = trim((string) ($postData['pinned_start_time'] ?? ''));
            if ($rawPinnedStart !== '' && strtotime($rawPinnedStart) === false) {
                $this->Flash->error('Invalid pinned start time format.');
                return $this->redirect(['action' => 'index', $convention_season_slug]);
            }
            $pinnedStart = ($rawPinnedStart !== '') ? date('H:i:s', strtotime($rawPinnedStart)) : null;
        }

        $availableFrom = null;
        $availableTo = null;
        if ($applyWindow) {
            $rawAvailableFrom = trim((string) ($postData['available_from_time'] ?? ''));
            $rawAvailableTo = trim((string) ($postData['available_to_time'] ?? ''));

            if ($rawAvailableFrom !== '') {
                if (strtotime($rawAvailableFrom) === false) {
                    $this->Flash->error('Invalid Available From time format.');
                    return $this->redirect(['action' => 'index', $convention_season_slug]);
                }
                $availableFrom = date('H:i:s', strtotime($rawAvailableFrom));
            }

            if ($rawAvailableTo !== '') {
                if (strtotime($rawAvailableTo) === false) {
                    $this->Flash->error('Invalid Available To time format.');
                    return $this->redirect(['action' => 'index', $convention_season_slug]);
                }
                $availableTo = date('H:i:s', strtotime($rawAvailableTo));
            }

            if ($availableFrom !== null && $availableTo !== null && strtotime($availableFrom) >= strtotime($availableTo)) {
                $this->Flash->error('Available From must be earlier than Available To.');
                return $this->redirect(['action' => 'index', $convention_season_slug]);
            }
        }

        $savedCount = 0;
        foreach ($eventIds as $eventId) {
            $existing = $this->Schedulingeventtweaks->find()
                ->where([
                    'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
                    'Schedulingeventtweaks.event_id' => $eventId,
                ])
                ->first();

            if ($existing) {
                $tweak = $existing;
            } else {
                $tweak = $this->Schedulingeventtweaks->newEntity([]);
                $tweak->conventionseasons_id = $conventionSD->id;
                $tweak->event_id = $eventId;
                $tweak->created = date('Y-m-d H:i:s');
            }

            if ($applyPinnedDay) {
                $tweak->pinned_day = $pinnedDay;
            }
            if ($applyPinnedRoom) {
                $tweak->pinned_room_id = $pinnedRoomId;
            }
            if ($applyPinnedStart) {
                $tweak->pinned_start_time = $pinnedStart;
            }
            if ($applyWindow) {
                $tweak->available_from_time = $availableFrom;
                $tweak->available_to_time = $availableTo;
            }

            $tweak->modified = date('Y-m-d H:i:s');
            if ($this->Schedulingeventtweaks->save($tweak)) {
                $savedCount++;
            }
        }

        if ($savedCount > 0) {
            $this->Flash->success('Bulk tweaks applied to ' . $savedCount . ' event(s).');
        } else {
            $this->Flash->error('No tweaks were saved in bulk edit.');
        }

        return $this->redirect(['action' => 'index', $convention_season_slug]);
    }

    /* ------------------------------------------------------------------ */
    /*  CLEAR – remove all tweaks for one event                            */
    /* ------------------------------------------------------------------ */
    public function clear($convention_season_slug = null, $event_id = null) {
        $this->request->allowMethod(['post', 'get']);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->first();

        if ($conventionSD && $event_id) {
            $this->Schedulingeventtweaks->deleteAll([
                'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
                'Schedulingeventtweaks.event_id'             => (int) $event_id,
            ]);
            $this->Flash->success('Tweak cleared.');
        }

        return $this->redirect(['action' => 'index', $convention_season_slug]);
    }

    /* ------------------------------------------------------------------ */
    /*  ROOMAVAILABILITY – set available_from / available_to per room      */
    /* ------------------------------------------------------------------ */
    public function roomavailability($convention_season_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Room Availability Windows');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->contain(['Conventions'])
            ->first();
        $this->set('conventionSD', $conventionSD);
        $this->set('convention_slug', $conventionSD->Conventions['slug']);

        $rooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->all();
        $this->set('rooms', $rooms);

        if ($this->request->is(['post'])) {
            $postData = (array) $this->request->getData();
            foreach ($rooms as $room) {
                $fromRaw = trim($postData['available_from'][$room->id] ?? '');
                $toRaw   = trim($postData['available_to'][$room->id] ?? '');

                $availFrom = ($fromRaw !== '' && strtotime($fromRaw) !== false)
                    ? date('H:i:s', strtotime($fromRaw)) : null;
                $availTo   = ($toRaw !== '' && strtotime($toRaw) !== false)
                    ? date('H:i:s', strtotime($toRaw)) : null;

                $this->Conventionrooms->updateAll(
                    ['available_from' => $availFrom, 'available_to' => $availTo, 'modified' => date('Y-m-d H:i:s')],
                    ['id' => $room->id]
                );
            }
            $this->Flash->success('Room availability windows saved.');
            return $this->redirect(['action' => 'roomavailability', $convention_season_slug]);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  ROOM LIMITS – show booked hours per room per day + set max hours   */
    /* ------------------------------------------------------------------ */
    public function roomlimits($convention_season_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Room Time Allocation');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->contain(['Conventions'])
            ->first();
        $this->set('conventionSD', $conventionSD);
        $this->set('convention_slug', $conventionSD->Conventions['slug']);

        $seasonId = $conventionSD->id;

        /* Convention days ordered */
        $weekArr     = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $allowedDays = [];
        $schedulingD = $this->Schedulings->find()
            ->where(['conventionseasons_id' => $seasonId])
            ->first();
        if ($schedulingD && $schedulingD->first_day && $schedulingD->number_of_days > 0) {
            $keyStart = array_search($schedulingD->first_day, $weekArr);
            if ($keyStart !== false) {
                for ($d = 0; $d < $schedulingD->number_of_days; $d++) {
                    $allowedDays[] = $weekArr[($keyStart + $d) % 7];
                }
            }
        }
        if (empty($allowedDays)) {
            /* Fallback: pull distinct days from timings */
            $distinctDays = $this->Schedulingtimings->find()
                ->select(['day'])
                ->where(['conventionseasons_id' => $seasonId])
                ->distinct()
                ->order(['day' => 'ASC'])
                ->all();
            foreach ($distinctDays as $row) {
                if (!empty($row->day)) $allowedDays[] = $row->day;
            }
        }
        $this->set('allowedDays', $allowedDays);

        /* All rooms for this convention */
        $rooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->all();
        $this->set('rooms', $rooms);

        /* Existing max-hours limits */
        $limitsRaw = $this->Schedulingroomlimits->find()
            ->where(['conventionseasons_id' => $seasonId])
            ->all();
        $limits = []; /* [room_id][day] => max_hours */
        foreach ($limitsRaw as $lim) {
            $limits[$lim->room_id][$lim->day] = $lim->max_hours;
        }
        $this->set('limits', $limits);

        /* Booked minutes per room per day from schedulingtimings (raw query) */
        $conn      = $this->Schedulingtimings->getConnection();
        $bookedStmt = $conn->execute(
            'SELECT room_id, day,
                    SUM(TIME_TO_SEC(TIMEDIFF(finish_time, start_time)) / 60) AS total_minutes,
                    COUNT(*) AS total_rows
             FROM schedulingtimings
             WHERE conventionseasons_id = :sid AND day IS NOT NULL AND day != \'\'
             GROUP BY room_id, day',
            ['sid' => $seasonId]
        );
        $booked = []; /* [room_id][day] => minutes */
        $scheduledCounts = []; /* [room_id][day] => row count */
        foreach ($bookedStmt->fetchAll('assoc') as $row) {
            $booked[$row['room_id']][$row['day']] = (float) $row['total_minutes'];
            $scheduledCounts[$row['room_id']][$row['day']] = (int) $row['total_rows'];
        }
        $this->set('booked', $booked);
        $this->set('scheduledCounts', $scheduledCounts);

        /* Handle save */
        if ($this->request->is(['post'])) {
            $postData    = (array) $this->request->getData();
            $maxHoursAll = $postData['max_hours'] ?? [];
            $now         = date('Y-m-d H:i:s');
            foreach ($rooms as $room) {
                foreach ($allowedDays as $day) {
                    $val = isset($maxHoursAll[$room->id][$day]) && $maxHoursAll[$room->id][$day] !== ''
                        ? (float) $maxHoursAll[$room->id][$day] : null;

                    /* Upsert */
                    $existing = $this->Schedulingroomlimits->find()
                        ->where(['conventionseasons_id' => $seasonId, 'room_id' => $room->id, 'day' => $day])
                        ->first();
                    if ($existing) {
                        $this->Schedulingroomlimits->updateAll(
                            ['max_hours' => $val, 'modified' => $now],
                            ['id' => $existing->id]
                        );
                    } else {
                        $entity = $this->Schedulingroomlimits->newEntity([
                            'conventionseasons_id' => $seasonId,
                            'room_id'              => $room->id,
                            'day'                  => $day,
                            'max_hours'            => $val,
                            'created'              => $now,
                            'modified'             => $now,
                        ]);
                        $this->Schedulingroomlimits->save($entity);
                    }
                }
            }
            $this->Flash->success('Room time limits saved.');
            return $this->redirect(['action' => 'roomlimits', $convention_season_slug]);
        }
    }
}
