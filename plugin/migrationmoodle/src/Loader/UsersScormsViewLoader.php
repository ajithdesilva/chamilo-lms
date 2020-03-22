<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Loader;

use Chamilo\CoreBundle\Entity\Session;
use Chamilo\PluginBundle\MigrationMoodle\Interfaces\LoaderInterface;

/**
 * Class UsersScormsViewLoader.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Loader
 */
class UsersScormsViewLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $incomingData)
    {
        $tblLpView = \Database::get_course_table(TABLE_LP_VIEW);
        $tblLpItemView = \Database::get_course_table(TABLE_LP_ITEM_VIEW);

        $sessionId = $this->getUserSubscriptionInSession($incomingData['user_id'], $incomingData['c_id']);

        $lpViewId = $this->getLpView(
            $incomingData['user_id'],
            $incomingData['lp_id'],
            $incomingData['c_id'],
            $sessionId
        );

        $itemView = [
            'c_id' => $incomingData['c_id'],
            'lp_item_id' => $incomingData['lp_item_id'],
            'lp_view_id' => $lpViewId,
            'view_count' => $incomingData['lp_item_view_count'],
            'status' => 'not attempted',
            'start_time' => time(),
            'total_time' => 0,
            'score' => 0,
            'max_score' => null,
        ];

        foreach (array_keys($itemView) as $key) {
            if (isset($incomingData['item_data'][$key])) {
                $itemView[$key] = $incomingData['item_data'][$key];
            }
        }

        $lpItemViewId = \Database::insert($tblLpItemView, $itemView);
        \Database::query("UPDATE $tblLpItemView SET id = iid WHERE iid = $lpItemViewId");

        \Database::query(
            "UPDATE $tblLpView
            SET last_item = {$incomingData['lp_item_id']},
                view_count = {$incomingData['lp_item_view_count']}
            WHERE iid = $lpViewId"
        );

        return $lpViewId;
    }

    /**
     * @param int $userId
     * @param int $courseId
     *
     * @throws \Exception
     *
     * @return int
     */
    private function getUserSubscriptionInSession($userId, $courseId)
    {
        $srcru = \Database::select(
            'session_id',
            \Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER),
            [
                'where' => [
                    'user_id = ? AND c_id = ?' => [$userId, $courseId],
                ],
            ]
        );

        if (empty($srcru)) {
            throw new \Exception("Session not found for user ($userId) with course ($courseId)");
        }

        return $srcru['session_id'];
    }

    /**
     * @param int $userId
     * @param int $lpId
     * @param int $cId
     * @param int $sessionId
     *
     * @return int
     */
    private function getLpView($userId, $lpId, $cId, $sessionId)
    {
        $lpView = \Database::select(
            'iid',
            \Database::get_course_table(TABLE_LP_VIEW),
            [
                'where' => [
                    'user_id = ? AND lp_id = ? AND c_id = ? AND session_id = ?' => [
                        $userId,
                        $lpId,
                        $cId,
                        $sessionId,
                    ],
                ],
                'order' => 'view_count DESC',
            ],
            'first'
        );

        if (empty($lpView)) {
            $tblLpView = \Database::get_course_table(TABLE_LP_VIEW);

            $lpView = [
                'c_id' => $cId,
                'lp_id' => $lpId,
                'user_id' => $userId,
                'view_count' => 1,
                'session_id' => $sessionId,
                'last_item' => 0,
            ];

            $lpViewId = \Database::insert($tblLpView, $lpView);
            \Database::query("UPDATE $tblLpView SET id = iid WHERE iid = $lpViewId");

            return $lpViewId;
        }

        return $lpView['iid'];
    }
}
