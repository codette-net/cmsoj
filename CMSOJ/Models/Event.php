<?php

namespace CMSOJ\Models;

use CMSOJ\Core\Model;

class Event extends Model
{
    protected string $table = 'events';
    protected string $primaryKey = 'id';

    /**
     * Get all events for a given UID and month.
     */
    public function getEventsForMonth(int $uid, int $year, int $month): array
    {
        // $stmt = $this->db()->prepare("
        //     SELECT *
        //     FROM events
        //     WHERE uid = ?
        //     AND (
        //         recurring != 'never'
        //         OR 
        //         (
        //             (datestart >= ? OR dateend >= ?)
        //             AND CAST(datestart AS DATE) <= ?
        //         )
        //     )
        //     ORDER BY datestart ASC
        // ");

        // $firstOfMonth = "$year-$month-01";
        // $lastOfMonth  = "$year-$month-31";

        // $stmt->execute([
        //     $uid,
        //     $firstOfMonth . " 00:00:00",
        //     $firstOfMonth . " 00:00:00",
        //     $lastOfMonth,
        // ]);

        $result = $this->list([
            'where' => [
                'uid' => $uid
            ],
            'sort' => 'datestart',
            'dir'  => 'asc',
        ]);
        return $result['data'];
        


        // return $stmt->fetchAll();
    }

    /** Create event (calendar uses this) */
    public function add(array $data): int
    {
        return $this->create($data);
    }

    /** Update event */
    public function updateEvent(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /** Delete event */
    public function remove(int $id): bool
    {
        return $this->delete($id);
    }
}
