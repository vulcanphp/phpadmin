<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use App\Models\User;
use VulcanPhp\PhpAdmin\Models\Visitor;
use VulcanPhp\PhpAdmin\Extensions\FusionChart\FusionCharts;
use VulcanPhp\PhpAdmin\Extensions\SvgMap\Map;
use VulcanPhp\Core\Foundation\Controller;
use VulcanPhp\Core\Helpers\Arr;
use VulcanPhp\Core\Helpers\Time;
use VulcanPhp\PhpAdmin\Models\Option;
use VulcanPhp\PhpAdmin\Models\Page;

class PhpAdminController extends Controller
{
    public function index()
    {
        if (phpadmin_enabled('users') && hasRights(['edit'])) {
            phpadmin()->addWidget(['icon' => 'user', 'text' => 'Total Users', 'count' => User::Cache()->load('total', fn () => User::total())]);
        }

        if (phpadmin_enabled('pages')  && hasRights(['edit'])) {
            phpadmin()->addWidget(['icon' => 'file-blank', 'text' => 'Number of Pages', 'count' => Page::Cache()->load('total', fn () => Page::total())]);
        }

        if (isSuperAdmin() && setting('enabled_visitor_analytics') === 'true') {
            Visitor::check();

            phpadmin()
                ->addWidget(['icon' => 'show', 'text' => 'Views in Today', 'count' => Visitor::total("date >=" . (is_sqlite() ? "date('now')" : "CURDATE()"))])
                ->addWidget(['icon' => 'objects-vertical-bottom', 'text' => 'Total Views', 'count' => Visitor::total()])
                ->addWidget(['icon' => 'map-pin', 'text' => 'Number of Countries', 'count' => Visitor::select('COUNT(DISTINCT country)')->fetch(\PDO::FETCH_COLUMN)->first()])
                ->addWidget(['icon' => 'globe', 'text' => 'Total Visitors', 'count' => Visitor::select('COUNT(DISTINCT ip)')->fetch(\PDO::FETCH_COLUMN)->first()]);

            $visitores = Visitor::select('COUNT(id) AS views, date')
                ->group('date')->order('date ASC')
                ->fetch(\PDO::FETCH_ASSOC)
                ->get()
                ->map(
                    fn ($visitor) => [
                        'label' => Time::format($visitor['date'], 'd F'),
                        'value' => $visitor['views']
                    ]
                )
                ->all();

            $chart = new FusionCharts("line", "ex1", "100%", 400, "monthly-chart", "json", [
                'chart' => [
                    "caption" => translate("Views in past " . (is_sqlite() ? 7 : 15) . " days"),
                    "yaxisname" => translate("Total Views in a Day"),
                    "subcaption" => sprintf('[%s - %s]', Arr::first($visitores)['label'] ?? '', Arr::last($visitores)['label'] ?? ''),
                    "numbersuffix" => " " . translate('Visits'),
                    "rotatelabels" => "1",
                    "setadaptiveymin" => "1",
                    "theme" => "fusion"
                ],
                'data' => $visitores
            ]);

            $chart->render();

            $chart = new FusionCharts("pie2d", "ex2", "100%", 400, "referer-pie", "json", [
                'chart' => [
                    'caption' => translate('Top 5 Referer Domains'),
                    'plottooltext' => '<b>$percentValue</b> of visitors from $label',
                    'showlegend' => '1',
                    'showpercentvalues' => '1',
                    'legendposition' => 'bottom',
                    'usedataplotcolorforlabels' => '1',
                    'theme' => 'fusion'
                ],
                'data' => Visitor::select('COUNT(id) AS views, referer')->group('referer')->order('views DESC')->limit(5)->fetch(\PDO::FETCH_ASSOC)->get()->map(fn ($referer) => ['value' => $referer['views'], 'label' => $referer['referer'] ?? 'No Referer'])->all()
            ]);

            $chart->render();

            $map = new Map(
                'countries',
                [
                    'views' => [
                        'name' =>  'Visitors',
                        'format' =>  '{0} Views',
                        'thousandSeparator' =>  ',',
                        'thresholdMax' =>  500000,
                        'thresholdMin' =>  0
                    ]
                ],
                Visitor::select('COUNT(id) AS views, country')
                    ->group('country')
                    ->fetch(\PDO::FETCH_ASSOC)
                    ->get()
                    ->mapWithKeys(fn ($visitor) => [
                        $visitor['country'] => [
                            'views' => $visitor['views']
                        ]
                    ])
                    ->to('json')
            );

            $map->render();
        }

        return phpadmin_view('index');
    }

    public function clonePage($id)
    {
        $page = Page::find($id);

        $page->id       = null;
        $page->title    = $page->title . ' (Copy)';
        $page->slug     = $page->slug . '-copy';

        if ($page->save()) {
            return response()->json(['message' => str_replace('#', $page->title, translate('Page: # has been cloned.'))]);
        }

        return response()->httpCode(500)->json(['message' => 'Failed! to clone record, please try again later.']);
    }

    public function siteKit()
    {
        if (request()->isPostBack()) {
            if (Option::saveOptions([input('block') => input('content')], 'sitekit')) {
                session()->setFlash('success', 'SiteKit Block Has Been Saved.');
            } else {
                session()->setFlash('warning', 'Failed to Save SiteKit Block.');
            }

            return response()->back();
        }

        return phpadmin_view('sitekit');
    }
}
