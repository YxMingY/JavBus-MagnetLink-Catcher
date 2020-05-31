<?php
namespace yxmingy\crawler;

class Parser
{
  private $url_manager;
  private $movies = [];
  public function __construct(URLManager $m)
  {
    $this->url_manager = $m;
  }
  public function parse(string $text,string $baseurl = "")
  {
    /*
    <a class="movie-box" href="https://www.javbus.com/OTIM-029">
    <p><span class="header">識別碼:</span> <span style="color:#CC0000;">OTIM-029</span>
    <span class="genre" onmouseover="hoverdiv(event,'star_vb3')" onmouseout="hoverdiv(event,'star_vb3')">
					<a href="https://www.javbus.com/star/vb3">松本いちか</a>
				</span>
				<a style="color:#333" rel="nofollow" title="滑鼠右鍵點擊並選擇【複製連結網址】" href="magnet:?xt=urn:btih:ED8F51D1FF2A7FC6874E8BBF565D2888BB91B7D6&amp;dn=393OTIM-029">
    */
    $mb_count = preg_match_all('/<a class="movie-box" href="(.+?)"/',$text,$mbs);
    $get_mn = preg_match('/<p><span class="header">識別碼:<\/span> <span style="color:#CC0000;">([A-Z0-9-]+?)<\/span>/',$text,$movie_num);
    if($mb_count > 0) {
      foreach($mbs[1] as $url) {
        if(preg_match('/javascript/',$url)) continue;
        //echo $url.PHP_EOL;
        //echo $url.PHP_EOL;
        $this->url_manager->addURL($url);
      }
      return;
    }
    if($get_mn > 0) {
      $get_cv = preg_match('/<div class="star-name"><a href="https:\/\/www.javbus.com\/star\/.+?" title=".*?">(.+?)<\/a><\/div>/',$text,$cv);
      $get_gid = preg_match('/var gid = (\w+?);/',$text,$gid);
      if($get_gid) {
        $xhr = $this->getMagnet($gid[1]);
        $get_link = preg_match('/<a style="color:#333" rel="nofollow" title="滑鼠右鍵點擊並選擇【複製連結網址】" href="(.+?)">/',$xhr,$link);
        $link = $get_link>0 ? $link[1] : "未知";
      }else{
        $link = "未知";
      }
      $cv = $get_cv>0 ? $cv[1] : "未知";
      $this->movies[] = [$movie_num[1],$cv,$link];
      return;
    }
  }
  public function print()
  {
    print_r($this->movies);
    $text = "";
    foreach($this->movies as $m) {
      $text .= $m[0]
        ."\n".
        $m[1]
        ."\n".
        $m[2]
        ."\n\n";
    }
    date_default_timezone_set('Asia/Shanghai');
    file_put_contents(date("m.d-H:i",time()).".txt",$text);
  }
  private function getMagnet($gid)
  {
    $stream_opts = [
      "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ],
      "http"=>[
        "method"=>"GET",
        "header"=>
        "authority: www.javbus.com\r\n"."accept: */*\r\n"."user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36\r\n"."x-requested-with: XMLHttpRequest\r\n"."sec-fetch-site: same-origin\r\n"."sec-fetch-mode: cors\r\n"."sec-fetch-dest: empty\r\n"."referer: https://www.javbus.com/JJDA-006\r\n"."accept-language: zh-CN,zh;q=0.9,zh-TW;q=0.8\r\n"."cookie: __cfduid=d7e71c54bbf1297efff0d29a0e036fcaa1590924254; PHPSESSID=o7veldem2pg2jj4ccbnv443bh6; existmag=mag; Hm_lvt_eaa57ca47dacb4ad4f5a257001a3457c=1590924306; Hm_lpvt_eaa57ca47dacb4ad4f5a257001a3457c=1590924551\r\n"
      ]
    ];
    $result = file_get_contents("https://www.javbus.com/ajax/uncledatoolsbyajax.php?gid=".$gid."&lang=zh&img=https://pics.javbus.com/cover/7orw_b.jpg&uc=0&floor=275",false, stream_context_create($stream_opts));
    return $result;
  }
}