<?php

 // Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.<br />");
}

$plugins->add_hook("youtubeminer_start", "youtubeminer");

function youtubeminer_info()
{
    return array(
        'name'          => 'Youtube Miner',
        'description'   => 'Mining youtube link from posts then display',
        'website'       => 'https://github.com/thangnguyenngoc/mybb-youtube-gallery',
        'author'        => 'Chii, https://github.com/thangnguyenngoc/mybb-youtube-gallery',
        'authorsite'    => 'https://github.com/thangnguyenngoc/mybb-youtube-gallery',
        'version'       => '1.0',
        'compatibility' => '16*,14*',
        "compatibility" => "16*",
        'guid'          => 'bbffff395800455bbb3e6694e1a9f32b'
    );
}

function youtubeminer_activate()
{
    global $db;
    
    require MYBB_ROOT."inc/adminfunctions_templates.php";
    require_once(MYBB_ROOT."admin/inc/functions_themes.php");

    $header = '<!-- Start VideoLightBox.com HEAD section -->
        <link rel="stylesheet" href="youtube_videolb/videolightbox.css" type="text/css" />
        <link rel="stylesheet" type="text/css" href="youtube_videolb/overlay-minimal.css"/>
        <script src="youtube_videolb/jquery.js" type="text/javascript"></script>
        <script src="youtube_videolb/swfobject.js" type="text/javascript"></script
        <!-- End VideoLightBox.com HEAD section -->
        <link rel="stylesheet" type="text/css" href="youtubeminer.css"/>';
    
    $template = array(
        "tid"        => NULL,
        "title"        => 'youtubeminer',
        "template"    => '<html>
<head>
<title>{\$mybb->settings[bbname]} - YouTube Gallery</title>
{\$headerinclude}
</head>
<body>
{\$header}
<br />
<center>
<!-- Start VideoLightBox.com BODY section -->
    <div class="navigation"><nav>
<a href="{\$previousLink}" id="prev">Prev</a>
<a href="{\$nextLink}" id="next">Next</a>
</nav></div>
    <div class="videogallery">{\$youtubeGallery}</div>
    <script src="youtube_videolb/jquery.tools.min.js" type="text/javascript"></script>
    <script src="youtube_videolb/videolightbox.js" type="text/javascript"></script>
    <!-- End VideoLightBox.com BODY section -->
</center>
<br>
<br>
{\$footer}
</body>
</html>',
        "sid"        => "-1",
        "version"    => "1.0",
        "dateline"    => TIME_NOW,
    );

    $db->insert_query("templates", $template);
    
    find_replace_templatesets("headerinclude", "#".preg_quote('{$stylesheets}').'#',
        $header.'{$stylesheets}');
}

function youtubeminer_deactivate()
{
    global $db, $header;
    
    require MYBB_ROOT."inc/adminfunctions_templates.php";

    require_once(MYBB_ROOT."admin/inc/functions_themes.php");
    
    find_replace_templatesets("headerinclude", "#".preg_quote($header).'#', '', 0);
    
    $db->delete_query("templates", "title='youtubeminer'");
}

function youtubeminer()
{
    global $db, $mybb, $templates;

    $previousLink = '';
    $nextLink = '';
    $start = 0;
    $length = 20;

    // build navigation link
    parse_str($_SERVER['QUERY_STRING'], $params);
    $start = 0;
    if (is_numeric($params['start']))
    {
        $start = (int)$params['start'];
    }

    $next = $start + 20;
    $previous = $start>20?($start-20):0;

    $host = $_SERVER['HTTP_HOST'];

    $previousLink = "http://" . $host . '/youtubeminer.php?start='.$previous;
    $nextLink = "http://" . $host . '/youtubeminer.php?start='.$next;

    if ($start==0)
    {
        $previousLink = '#';
    }

    //build gallery

    $count = 0;
    $query = $db->query("SELECT p.pid,p.subject,p.message FROM ".TABLE_PREFIX."posts p WHERE p.message LIKE \"%[video=youtube]%[/video]%\" ORDER BY p.subject LIMIT ".$start.",".$length);
    $youtubeGallery = '';
    $count = 0;
    while($post = $db->fetch_array($query))
    {
        preg_match("/\\[video=youtube\\](.*?)\\[\\/video\\]/m", $post['message'], $matches);
        if (is_array($matches) && count($matches) > 1)
        {
            //TODO: remove watch? from youtube link
            $videoid = getYoutubeIdFromUrl($matches[1]);
            if ($videoid==false) continue;
            $videolink = 'http://youtube.com/v/'.$videoid.'?autoplay=1&rel=0&enablejsapi=1&playerapiid=ytplayer"';
            $imagelink = 'http://img.youtube.com/vi/'.$videoid.'/default.jpg';
            $youtubeGallery .= '<a class="voverlay" href="'.$videolink.' title="'.$post['subject'].'"><img src="'.$imagelink.'" alt="'.$post['subject'].'" /><span></span></a>';
            $count++;
        }
    }

    if ($count<$length)
    {
        $nextLink = '#';
    }

    $data = array(
        'previousLink' => $previousLink,
        'nextLink' => $nextLink,
        'youtubeGallery' => $youtubeGallery
    );

    return $data;
}

/**
 * Get Youtube video ID from URL
 *
 * @param string $url
 * @return mixed Youtube video ID or FALSE if not found
 */
function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
            return $qs['v'];
        }else if($qs['vi']){
            return $qs['vi'];
        }
    }
    if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}

?>