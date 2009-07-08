<?php
/*
Plugin Name: TwitterGrid
Plugin URI: http://www.tom-hengst.de/wordpress-plugins
Description: Displays the Twitter-Images of all your friends as a mosaic in the sidebar of your blog. Check out more <a href="http://www.tom-hengst.de/wordpress-plugins">Wordpress Plugins</a> by <a href="http://www.tom-hengst.de">Tom</a>.
Version: 0.1
Author: Tom Hengst
Author URI: http://www.tom-hengst.de
*/

/**
 * v0.1 07.07.2009 initial release
 */
class TwitterGrid {
  var $id;
  var $title;
  var $plugin_url;
  var $version;
  var $name;
  var $url;
  var $options;
  var $locale;
  var $cache_file;

  function TwitterGrid() {
    $this->id         = 'twittergrid';
    $this->title      = 'TwitterGrid';
    $this->version    = '0.1';
    $this->plugin_url = 'http://www.tom-hengst.de/wordpress-plugins';
    $this->name       = 'TwitterGrid v'. $this->version;
    $this->url        = get_bloginfo('wpurl'). '/wp-content/plugins/' . $this->id;

	  $this->locale     = get_locale();
    $this->path       = dirname(__FILE__);
    $this->cache_file = $this->path . '/cache/friends.html';

	  if(empty($this->locale)) {
		  $this->locale = 'en_US';
    }

    load_textdomain($this->id, sprintf('%s/%s.mo', $this->path, $this->locale));

    $this->loadOptions();

    if(!is_admin()) {
      add_filter('wp_head', array(&$this, 'blogHeader'));
    }
    else {
      add_action('admin_menu', array( &$this, 'optionMenu')); 
    }

    add_action('widgets_init', array( &$this, 'initWidget')); 
  }

  function optionMenu() {
    add_options_page($this->title, $this->title, 8, __FILE__, array(&$this, 'optionMenuPage'));
  }

  function optionMenuPage() {
?>
<div class="wrap">
<h2><?=$this->title?></h2>
<div align="center"><p><?=$this->name?> <a href="<?php print( $this->plugin_url ); ?>" target="_blank">Plugin Homepage</a></p></div> 
<?php
  if(isset($_POST[$this->id])) {
    /**
     * nasty checkbox handling
     */
    foreach(array('link', 'nofollow', 'show_twitter_link', 'target_blank') as $field ) {
      if(!isset($_POST[$this->id][$field])) {
        $_POST[$this->id][$field] = '0';
      }
    }
    
    @unlink($this->cache_file);

    $this->updateOptions( $_POST[ $this->id ] );

    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $this->id) . '</strong></p></div>'; 
  }
?>
<form method="post" action="options-general.php?page=<?=$this->id?>/<?=$this->id?>.php">

<table class="form-table">
<?php if(!file_exists($this->path.'/cache/') || !is_writeable($this->path.'/cache/')): ?>
<tr valign="top"><th scope="row" colspan="3"><span style="color:red;"><?php _e('Warning! The cachedirectory is missing or not writeable!', $this->id); ?></span><br /><em><?php echo $this->path; ?>/cache</em></th></tr>
<?php endif; ?>

<tr valign="top">
  <th scope="row"><?php _e('Title', $this->id); ?></th>
  <td colspan="3"><input name="twittergrid[title]" type="text" id="" class="code" value="<?=$this->options['title']?>" /><br /><?php _e('Title is shown above the Widget. If left empty can break your layout in widget mode!', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Username', $this->id); ?></th>
  <td colspan="3"><input name="twittergrid[username]" type="text" id="" class="code" value="<?=$this->options['username']?>" />
  <br /><?php _e('Your Twitter username!', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Limit', $this->id); ?></th>
  <td colspan="3"><input name="twittergrid[limit]" type="text" id="" class="code" value="<?=$this->options['limit']?>" />
  <br /><?php _e('Max. number of images to display in grid!', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Width', $this->id); ?></th>
  <td colspan="3"><input name="twittergrid[width]" type="text" id="" class="code" value="<?=$this->options['width']?>" />
  <br /><?php _e('Width of grid. Have in mind, that you get 2px extra padding for each image per line.', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Height', $this->id); ?></th>
  <td colspan="3"><input name="twittergrid[height]" type="text" id="" class="code" value="<?=$this->options['height']?>" />
  <br /><?php _e('Height of grid!', $this->id); ?></td>
</tr>


<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="twittergrid[link]" type="checkbox" id="" value="1" <?php echo $this->options['link']=='1'?'checked="checked"':''; ?> />
<?php _e('Link image to twitter accounts?', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="twittergrid[nofollow]" type="checkbox" id="" value="1" <?php echo $this->options['nofollow']=='1'?'checked="checked"':''; ?> />
<?php _e('Set the link to relation nofollow?', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="twittergrid[target_blank]" type="checkbox" id="" value="1" <?php echo $this->options['target_blank']=='1'?'checked="checked"':''; ?> />
<?php _e('Open link in new window?', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="twittergrid[show_twitter_link]" type="checkbox" id="" value="1" <?php echo $this->options['link']=='1'?'checked="checked"':''; ?> />
<?php _e('Show a link to my twitter profile below the grid?', $this->id); ?></label>
</th>
</tr>


</table>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('save', $this->id); ?>" class="button" />
</p>
</form>

</div>
<?php
  }

  function updateOptions($options) {

    foreach($this->options as $k => $v) {
      if(array_key_exists( $k, $options)) {
        $this->options[ $k ] = trim($options[ $k ]);
      }
    }

		update_option($this->id, $this->options);
	}
  
  function loadOptions() {
    $this->options = get_option( $this->id );

    if( !$this->options ) {
      $this->options = array(
        'installed' => time(),
        'username' => '',
        'link' => 1,
        'nofollow' => 1,
        'limit' => 18,
        'target_blank' => 1,
        'width' => 160,
        'height' => 400,
        'last_check' => 0,
        'show_twitter_link' => 1,
        'title' => 'TwitterGrid'
			);

      add_option($this->id, $this->options, $this->name, 'yes');

      if(is_admin()) {
        add_filter('admin_footer', array(&$this, 'addAdminFooter'));
      }
    }
  }

  function initWidget() {
    if(function_exists('register_sidebar_widget')) {
      register_sidebar_widget($this->title . ' Widget', array($this, 'showWidget'), null, 'widget_twittergrid');
    }
  }

  function showWidget( $args ) {
    extract($args);
    printf( '%s%s%s%s%s%s', $before_widget, $before_title, $this->options['title'], $after_title, $this->getCode(), $after_widget );
  }

  function blogHeader() {
    printf('<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version);
    printf('<link rel="stylesheet" href="%s/styles/%s.css" type="text/css" media="screen" />'. "\n", $this->url, $this->id);
  }

  function getToken($data, $pattern) {
    if(preg_match('|<' . $pattern . '>(.*?)</' . $pattern . '>|s', $data, $matches)) {
      return $matches[1];
    }
    return '';
  }

  function getFriends($user) {
    if(empty($user)) {
      return false;
    }
    
    if(!class_exists('Snoopy')) {
      if(!@include_once(ABSPATH . WPINC . '/class-snoopy.php')) {
        return false;
      }
    }
  
    $Snoopy = new Snoopy();
    
    /**
     * not the best way, but we can't assume that every webhost simplexml installed
     */
    if(@$Snoopy->fetch('http://twitter.com/statuses/friends/' . $user . '.xml')) {
      if(!empty($Snoopy->results)) {
        if(preg_match_all('/<user>(.*?)<\/user>/s', $Snoopy->results, $matches)) {
          $result = array();
          foreach($matches[0] as $matche) {
            $result[] = array(
              $this->getToken($matche, 'screen_name'),
              $this->getToken($matche, 'profile_image_url')
            );
          }
          return $result;
        }
      }
    }
    return false;
  }

  function getCode() {
    
    $create = false;
    
    if(!file_exists($this->cache_file)) {
      $create = true;
    }
    elseif(time() - filemtime($this->cache_file) > (3600 * 3)) {
      $create = true;
    }
    
    if(!$create) {
      return file_get_contents($this->cache_file);
    }
    
    $friends = $this->getFriends($this->options['username']);
    
    $count = count($friends);
    
    if($friends && $count > 0) {
      if($count > intval($this->options['limit'])) {
        $friends = array_slice($friends, 0, intval($this->options['limit']));
      }

      $data = '';

      foreach($friends as $friend) {

        $item = sprintf(
          '<img src="%s" title="%s" width="48" height="48" alt="%s" />',
          $friend[1],
          $friend[0],
          $friend[0]
        );
        
        if(intval($this->options['link']) == 1) {
          $item = sprintf(
            '<a href="http://twitter.com/%s" class="snap_noshots" %s%s>%s</a>',
            $friend[0],
            $this->options['target_blank'] == 1 ? ' target="_blank"' : '',
            $this->options['nofollow'] == 1 ? ' rel="nofollow"' : '',
            $item
          );
        }

        $data .= $item;
      }

      $data = '<div id="twittergrid">'. $data . (intval($this->options['show_twitter_link'])==1?'<strong><a href="http://twitter.com/'.$this->options['username'].'" rel="nofollow" target="_blank">'.__('Follow me!', $this->id).'</a></strong>':'').'<div><a href="http://www.tom-hengst.de/wordpress-plugins" target="_blank" class="snap_noshots">TwitterGrid</a> by <a href="http://www.tom-hengst.de" target="_blank" class="snap_noshots">Tom</a></div></div>';

      if(is_writeable($this->path. '/cache')) {
        file_put_contents($this->cache_file, $data);
      }
      
      return $data;
    }
    
    return '';
  }
}

function twittergrid_display() {

  global $TwitterGrid;

  if($TwitterGrid) {
    echo $TwitterGrid->getcode();
  }
}

add_action( 'plugins_loaded', create_function( '$TwitterGrid_5kqll', 'global $TwitterGrid; $TwitterGrid = new TwitterGrid();' ) );

?>