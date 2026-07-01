<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SchedulingtimingsTable extends Table {

    public function initialize(array $config): void
    {
		$this->belongsTo('Conventionseasons', [
            'className' => 'Conventionseasons',
            'foreignKey' => 'conventionseasons_id',
            'propertyName' => 'Conventionseasons'
        ]);
		$this->belongsTo('Conventions', [
            'className' => 'Conventions',
            'foreignKey' => 'convention_id',
            'propertyName' => 'Conventions'
        ]);
		
		$this->belongsTo('Seasons', [
            'className' => 'Seasons',
            'foreignKey' => 'season_id',
            'propertyName' => 'Seasons'
        ]);
		
		$this->belongsTo('Conventionregistrations', [
            'className' => 'Conventionregistrations',
            'foreignKey' => 'conventionregistration_id',
            'propertyName' => 'Conventionregistrations'
        ]);
		
		$this->belongsTo('Events', [
            'className' => 'Events',
            'foreignKey' => 'event_id',
            'propertyName' => 'Events'
        ]);
		
		$this->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'user_id',
            'propertyName' => 'Users'
        ]);
		
		$this->belongsTo('Conventionrooms', [
            'className' => 'Conventionrooms',
            'foreignKey' => 'room_id',
            'propertyName' => 'Conventionrooms'
        ]);
		
		$this->belongsTo('Opponentuser', [
            'className' => 'Users',
            'foreignKey' => 'user_id_opponent',
            'propertyName' => 'Opponentuser'
        ]);
    }

    public function beforeSave($event, $entity, $options)
    {
        if (empty($entity->conventionseasons_id) || empty($entity->sch_date_time) || empty($entity->start_time) || empty($entity->finish_time)) {
            return true;
        }

        $conventionSeasonsId = (int)$entity->conventionseasons_id;
        $roomId = (int)($entity->room_id ?? 0);
        $startTime = date('H:i:s', strtotime((string)$entity->start_time));
        $finishTime = date('H:i:s', strtotime((string)$entity->finish_time));
        $startTs = strtotime((string)$entity->sch_date_time . ' ' . $startTime);
        $finishTs = strtotime((string)$entity->sch_date_time . ' ' . $finishTime);
        if ($startTs === false || $finishTs === false || $finishTs <= $startTs) {
            return true;
        }

        $studentIds = array_values(array_unique(array_filter([
            (int)($entity->user_id ?? 0),
            (int)($entity->user_id_opponent ?? 0),
        ], function ($value) {
            return $value > 0;
        })));

        $conflictQuery = $this->find()
            ->select(['id', 'room_id', 'user_id', 'user_id_opponent', 'sch_date_time', 'start_time', 'finish_time'])
            ->where([
                'Schedulingtimings.conventionseasons_id' => $conventionSeasonsId,
                'Schedulingtimings.id !=' => (int)($entity->id ?? 0),
                'Schedulingtimings.sch_date_time IS NOT' => null,
                'Schedulingtimings.start_time IS NOT' => null,
                'Schedulingtimings.finish_time IS NOT' => null,
            ]);

        $conflictRows = [];
        if ($roomId > 0) {
            $conflictRows = array_merge(
                $conflictRows,
                $conflictQuery
                    ->where(['Schedulingtimings.room_id' => $roomId])
                    ->all()
                    ->toArray()
            );
        }

        if (!empty($studentIds)) {
            $studentRows = $this->find()
                ->select(['id', 'room_id', 'user_id', 'user_id_opponent', 'sch_date_time', 'start_time', 'finish_time'])
                ->where([
                    'Schedulingtimings.conventionseasons_id' => $conventionSeasonsId,
                    'Schedulingtimings.id !=' => (int)($entity->id ?? 0),
                    'Schedulingtimings.sch_date_time IS NOT' => null,
                    'Schedulingtimings.start_time IS NOT' => null,
                    'Schedulingtimings.finish_time IS NOT' => null,
                ])
                ->andWhere(function ($exp) use ($studentIds) {
                    $conditions = [];
                    foreach ($studentIds as $studentId) {
                        $conditions[] = ['Schedulingtimings.user_id' => $studentId];
                        $conditions[] = ['Schedulingtimings.user_id_opponent' => $studentId];
                    }
                    return $exp->or_($conditions);
                })
                ->all()
                ->toArray();
            $conflictRows = array_merge($conflictRows, $studentRows);
        }

        foreach ($conflictRows as $conflictRow) {
            $conflictStartTs = strtotime((string)$conflictRow->sch_date_time . ' ' . date('H:i:s', strtotime((string)$conflictRow->start_time)));
            $conflictFinishTs = strtotime((string)$conflictRow->sch_date_time . ' ' . date('H:i:s', strtotime((string)$conflictRow->finish_time)));
            if ($conflictStartTs === false || $conflictFinishTs === false) {
                continue;
            }

            if ($startTs < $conflictFinishTs && $conflictStartTs < $finishTs) {
                $entity->setError('sch_date_time', 'Overlapping room or participant booking detected.');
                return false;
            }
        }

        return true;
    }
	
	      

}

?>