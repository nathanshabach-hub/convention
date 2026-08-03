<?php
namespace App\Test\TestCase\Controller;

use App\Controller\AppController;
use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\TestSuite\TestCase;

class AppControllerSeasonTest extends TestCase
{
    public function testGetCurrentSeasonUsesTheActiveRegistrationWindow(): void
    {
        $controller = new AppController(new ServerRequest(), new Response());
        $controller->initialize();

        $today = date('Y-m-d');
        $expectedSeasonId = (int)ConnectionManager::get('default')
            ->execute(
                'SELECT cs.season_id
                 FROM conventionseasons cs
                 INNER JOIN seasons s ON s.id = cs.season_id
                 WHERE s.status = 1
                   AND cs.registration_start_date <= :today
                   AND cs.registration_end_date >= :today
                 ORDER BY s.season_year DESC, s.id DESC
                 LIMIT 1',
                ['today' => $today]
            )
            ->fetchColumn();

        if ($expectedSeasonId === 0) {
            $expectedSeasonId = (int)ConnectionManager::get('default')
                ->execute(
                    'SELECT id
                     FROM seasons
                     WHERE status = 1
                     ORDER BY season_year DESC, id DESC
                     LIMIT 1'
                )
                ->fetchColumn();
        }

        $this->assertSame($expectedSeasonId, $controller->getCurrentSeason());
    }

    public function testGetCurrentSeasonConventionsReturnsActiveSeasonConventions(): void
    {
        $controller = new AppController(new ServerRequest(), new Response());
        $controller->initialize();

        $seasonId = $controller->getCurrentSeason();
        $seasonD = $controller->Seasons->find()->where(['Seasons.id' => $seasonId])->first();

        $expectedConventionIds = [];
        foreach ($controller->Conventionseasons->find()
            ->where([
                'Conventionseasons.season_id' => $seasonId,
                'Conventionseasons.season_year' => $seasonD->season_year,
            ])
            ->order(['Conventionseasons.id' => 'ASC'])
            ->all() as $conventionSeason) {
            $expectedConventionIds[] = (int)$conventionSeason->convention_id;
        }

        $conventions = $controller->getCurrentSeasonConventions($seasonId, $seasonD->season_year);

        $this->assertNotEmpty($conventions);
        $this->assertSame($expectedConventionIds, array_map('intval', array_keys($conventions)));
    }

    public function testGetSchoolConventionsForCurrentSeasonReturnsRegisteredConventionsFirst(): void
    {
        $controller = new AppController(new ServerRequest(), new Response());
        $controller->initialize();

        $seasonId = $controller->getCurrentSeason();
        $seasonD = $controller->Seasons->find()->where(['Seasons.id' => $seasonId])->first();

        $conventions = $controller->getSchoolConventionsForCurrentSeason(5806, $seasonId, $seasonD->season_year);

        $this->assertNotEmpty($conventions);
        $this->assertSame([24], array_map('intval', array_keys($conventions)));
    }
}
