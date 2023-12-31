<?php

namespace VulcanPhp\PhpAdmin\Extensions\Whoer;

use Exception;
use VulcanPhp\PhpAdmin\Extensions\Whoer\UserAgent;
use MaxMind\Db\Reader;
use PharData;
use Throwable;
use VulcanPhp\EasyCurl\EasyCurl;
use VulcanPhp\FileSystem\File;

class Whoer
{
    /**
     * @var $useragent
     */
    protected array $agent;

    /**
     * Init New Client Object
     * @return Whoer
     */
    public static function init(): static
    {
        return new static;
    }

    public function agent(string $key, $default = null)
    {
        if (!isset($this->agent)) {
            $this->agent = UserAgent::parse();
        }

        return $this->agent[$key] ?? $default;
    }

    public function isBot(): bool
    {
        return $this->botName() !== null;
    }

    public function botName(): ?string
    {
        if (preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosuserAgentcrawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i', request()->userAgent(), $bots)) {
            return $bots[0] ?? null;
        }

        return null;
    }

    public function refererDomain(): ?string
    {
        $domain = !empty(request()->referer()) ? trim(str_ireplace(['www.'], '', parse_url(request()->referer(), PHP_URL_HOST) ?? '')) : null;
        return !empty($domain) ? $domain : null;
    }

    public function country(?string $ip = null): ?string
    {
        if ($ip === null) {
            $ip = request()->ip();
        }

        $file = File::choose(__DIR__ . '/etc/GeoLite2-Country.mmdb');

        if (!$file->exists() || ($file->exists() && intval($file->mtime()) < strtotime('-6 month'))) {
            $this->download_geoip();
        }

        if ($file->exists()) {
            try {
                $reader = new Reader($file->path());
                return $reader->get($ip)['country']['iso_code'] ?? null;
            } catch (Throwable $e) {
            }
        }

        return null;
    }

    protected function download_geoip(): void
    {
        $log_file   = __DIR__ . '/etc/api.log';
        $key        = trim(setting('maxmind_api_key', ''));

        if (empty($key) && is_dev()) {
            throw new Exception('MaxMin Api Key does not specified');
        }

        if (!empty($key) && file_exists($log_file) && (json_decode(file_get_contents($log_file), true)['error'] ?? false) === false) {
            try {
                $download = __DIR__ . '/etc/GeoLite2-Country.tar.gz';
                $http = EasyCurl::setDownloadFile($download, true)->get('https://download.maxmind.com/app/geoip_download', [
                    'edition_id' => 'GeoLite2-Country',
                    'suffix' => 'tar.gz',
                    'license_key' => $key
                ]);
                if ($http->status() === 200 && file_exists($download)) {
                    try {
                        $phar = new PharData($download);
                        $file_path = $phar->current()->getFileName() . '/GeoLite2-Country.mmdb';
                        $phar->extractTo(__DIR__ . '/etc/', $file_path, true);
                        if (file_exists(__DIR__ . '/etc/' . $file_path)) {
                            rename(__DIR__ . '/etc/' . $file_path, __DIR__ . '/etc/GeoLite2-Country.mmdb');
                            rmdir(__DIR__ . '/etc/' . $phar->current()->getFileName());
                            unlink($download);
                            unset($phar);
                        } else {
                            throw new Exception('Invalid Database Zip Format.');
                        }
                    } catch (Throwable $e) {
                        throw new Exception('Failed to Unzip GeoLite2-Country.tar.gz, error: ' . $e->getMessage());
                    }
                } else {
                    throw new Exception('Failed to download geoip database');
                }
            } catch (Throwable $e) {
                // stop downloading
                file_put_contents($log_file, json_encode([
                    'error' => true,
                    'message' => $e->getMessage()
                ]));
            }
        }
    }
}
