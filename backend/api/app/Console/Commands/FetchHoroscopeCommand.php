<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Horoscope;

class FetchHoroscopeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-horoscope-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch daily horoscope from PTT.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始抓取 PTT 雙子座每日運勢...');

        $client = new Client();
        $url = 'https://www.ptt.cc/bbs/Gemini/index.html';

        try {
            $response = $client->request('GET', $url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);

            $today = now()->format('Y/m/d');
            $targetTitle = "[情報] {$today} 雙子座每日星座運勢";

            $link = $crawler->filter('div.r-ent div.title a')->each(function (Crawler $node) use ($targetTitle) {
                if (str_contains($node->text(), $targetTitle)) {
                    return $node->attr('href');
                }
            });

            $articleUrl = collect($link)->filter()->first();

            if ($articleUrl) {
                $this->info("找到文章連結: https://www.ptt.cc" . $articleUrl);
                $articleResponse = $client->request('GET', "https://www.ptt.cc" . $articleUrl);
                $articleHtml = (string) $articleResponse->getBody();
                $articleCrawler = new Crawler($articleHtml);

                $content = $articleCrawler->filter('div#main-content')->text();

                // 提取內文，這裡需要根據實際內容進行調整
                // 假設內文格式固定，我們可以嘗試用正則表達式或字串處理來提取
                $lines = explode("\n", $content);
                $horoscopeContent = [];
                $startExtracting = false;
                foreach ($lines as $line) {
                    // 找到內文開始的標記，例如 "第一行：雙子座   晴"
                    if (str_contains($line, '雙子座') && str_contains($line, '晴')) {
                        $startExtracting = true;
                        continue;
                    }

                    if ($startExtracting) {
                        // 假設內文結束於某些特定字串，例如 "--"
                        if (str_contains($line, '--') || empty(trim($line))) {
                            // 遇到空行或分隔線，停止提取
                            if (!empty(trim($line))) {
                                $horoscopeContent[] = trim($line);
                            }
                            // 檢查是否已經提取到足夠的內容，例如幸運色
                            if (str_contains($line, '幸運色')) {
                                break;
                            }
                            continue;
                        }
                        $horoscopeContent[] = trim($line);
                    }
                }

                $this->info('抓取到的星座運勢內容:');
                $fullContent = implode("\n", $horoscopeContent);
                foreach ($horoscopeContent as $line) {
                    $this->info($line);
                }

                // 儲存到資料庫
                Horoscope::updateOrCreate(
                    ['date' => now()->toDateString(), 'sign' => '雙子座'],
                    ['content' => $fullContent, 'lucky_color' => $this->extractLuckyColor($horoscopeContent)]
                );

                $this->info('星座運勢已儲存到資料庫。');

            } else {
                $this->warn('今天沒有找到符合條件的雙子座每日運勢文章。');
            }

        } catch (\Exception $e) {
            $this->error('抓取失敗: ' . $e->getMessage());
        }
    }

    /**
     * 從運勢內容中提取幸運色。
     *
     * @param array $contentLines
     * @return string|null
     */
    protected function extractLuckyColor(array $contentLines): ?string
    {
        foreach ($contentLines as $line) {
            if (str_contains($line, '幸運色是')) {
                preg_match('/幸運色是(.+?)。/', $line, $matches);
                if (isset($matches[1])) {
                    return trim($matches[1]);
                }
            }
        }
        return null;
    }
}
