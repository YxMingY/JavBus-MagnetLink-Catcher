<?php
namespace yxmingy\crawler;

class Scheduler
{
  private $active = true;
  protected $keyword = "";
  private $parser;
  private $url_manager;
  public function __construct(string $keyword)
  {
    $this->keyword = $keyword;
  }
  public function init()
  {
    $baseurl = "https://www.javbus.com/";
    $this->url_manager = new URLManager();
    $this->parser = new Parser($this->url_manager);
    $this->parser->parse($this->download($baseurl));
  }
  public function start()
  {
    while($this->active) {
      $url = $this->url_manager->getURLToCrawl();
      if($url === null) {
        $this->parser->print();
        die(base64_decode("MjMz"));
      }
      if(preg_match('/\w$/',$url)) {
        $burl = substr($url,0,strrpos($url,"/")+1);
      } else {
        $burl = $url;
      }
      $this->parser->parse($this->download($url),$burl);
    }
  }
  private function download(string $url):?string
  {
    return 
      preg_match('/https/',$url)
      ?
      $this->getSSLPage($url)
      :
      $this->getPage($url);
  }
  private function getPage(string $url):string
  {
    $get = null;
    try{
      $get = file_get_contents($url);
    }catch(Exception $e) {
      
    }
    return $get;
  }
  private function getSSLPage(string $url):?string
  {
    $stream_opts = [
      "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ]
    ];
    $result = file_get_contents($url,false, stream_context_create($stream_opts));
    return $result;
  }
}