<?php

/*
Plugin Name: Gallery Voting
Plugin URI: http://tribulant.com
Description: Voting/likes for the WordPress <code>[gallery]</code> shortcode photos/images.
Author: Tribulant Software
Version: 1.2
Author URI: http://tribulant.com
Text Domain: gallery-voting
Domain Path: /languages
*/

require ABSPATH . 'wp-content/plugins/gallery-voting-master/Custom_Comments_List_Table.php';

if (!class_exists('GalleryVoting')) {
	class GalleryVoting {

        const DISPLAY_COMMENT = 3;
		function __construct() {
			$this -> initialize_options();
		}
		
		function initialize_options() {
			add_option('gallery_voting_css', '
			@media (max-width: 768px) {
				.gallery-item {
					width: 50% !important;
				}
			}
			
			@media (max-width: 480px) { 
				.gallery-item {
					width: 100% !important;
				}
			}
			
			.gallery {
				margin: auto;
			}
			.gallery-item {
				float: left;
				margin-top: 10px;
				text-align: center;
			/*	width: {$itemwidth}%; */
			}
			.gallery img {
				border: 2px solid #cfcfcf;
			}
			.gallery-caption {
				margin-left: 0;
				}');
				
			add_option('gallery_voting_usersallowed', "all");
			add_option('gallery_voting_max_all', "3");
			add_option('gallery_voting_max_same', "1");
			add_option('gallery_voting_tracking', "ipaddress");
            add_option('gallery_comment_max_all', "3");
            add_option('gallery_comment_max_same', "1");
	
//			global $wpdb;
//			$name = $wpdb -> prefix . 'galleryvotes';
//			$query = "SHOW TABLES LIKE '" . $name . "'";
//			if (!$wpdb -> get_var($query)) {
//				$query = "CREATE TABLE `" . $name . "` (";
//				$query .= "`id` INT NOT NULL AUTO_INCREMENT,";
//                $query .= "`page_id` INT NOT NULL DEFAULT 0,";
//                $query .= "`type` INT NOT NULL DEFAULT 0,";
//				$query .= "`ip_address` VARCHAR(100) NOT NULL DEFAULT '',";
//                $query .= "`source_url` VARCHAR(255) NOT NULL DEFAULT '',";
//				$query .= "`attachment_id` INT(11) NOT NULL DEFAULT '0',";
//                $query .= "`email` VARCHAR(200) NOT NULL DEFAULT '',";
//                $query .= "`comment` text NOT NULL,";
//                $query .= "`auth_type` tinyint NOT NULL DEFAULT 0,";
//                $query .= "`page_url` VARCHAR(200) NOT NULL DEFAULT '',";
//				$query .= "`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',";
//				$query .= "`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',";
//				$query .= "PRIMARY KEY (`id`)";
//				$query .= ") ENGINE=MyISAM AUTO_INCREMENT=1 CHARSET=UTF8 COLLATE=utf8_general_ci;";
//				$wpdb -> query($query);
//			}
		}
		
		function debug($var = null) {
			echo '<pre>' . print_r($var, true) . '</pre>';
		}

        function getInitVoteCommentNum($wpdb, $pageId, $typeId, $arrDataId, $ipFilter)
        {
            if (empty($arrDataId)) {
                return [];
            }
            $sDataId = implode(',', $arrDataId);
            $typeAndIpFilter = " and type = {$typeId}";
            if ($ipFilter && !empty($_SERVER['REMOTE_ADDR'])) {
                $typeAndIpFilter .= ' and ip_address = \'' . $_SERVER['REMOTE_ADDR'] . '\'';
            }
            if ($typeId == 1) {
                $typeAndIpFilter .= ' and auth_type = 0';
            }
            $countQuery = "SELECT attachment_id, COUNT(`id`) as cnt FROM `{$wpdb -> prefix}galleryvotes` WHERE page_id={$pageId} and `attachment_id` in ({$sDataId}){$typeAndIpFilter} group by attachment_id";
            $arrAllRet = $wpdb->get_results($countQuery, ARRAY_A);
            empty($arrAllRet) && $arrAllRet = [];
            return array_column($arrAllRet, 'cnt', 'attachment_id');
        }



        function getCommentFromDb($wpdb, $pageId, $attachment_id)
        {
            $limitCount = self::DISPLAY_COMMENT;
            $commentQuery = "SELECT email,comment FROM `{$wpdb -> prefix}galleryvotes` WHERE page_id='{$pageId}' and `attachment_id` = '{$attachment_id}' and type=1 order by id desc limit {$limitCount}";
            $rows = $wpdb->get_results($commentQuery, ARRAY_A);
            return $rows;
        }
        function getCommentList($arrRows)
        {
            if (count($arrRows) <= 0) {
                return '';
            }

            $ret = '<div ><span class="title"> Comments</span></div><ul>';
            foreach ($arrRows as $k => $row) {
                $email = $row['email'] ?? '';
                $comment = $row['comment'] ?? '';
                if ($email) {
                    $email = explode('@', $email)[0];
                }
                if (strlen($comment) > 100) {
                    $comment = substr($comment, 0,100) . '...';
                }
                $ret .= '<li class="cpcomment">' . $comment . '</li>';
              //  $ret .= '<li class="cpcomment">(' . $email . ')' . $comment . '</li>';
            }
            $ret .= "</ul>";
            return $ret;
        }
		
		function plugins_loaded() {

		}
		
		// Add settings link on plugin page
		function plugin_action_links($links) {
		     $curLink = '<a href="' . admin_url('options-general.php?page=gallery-voting') . '">' . __('Settings', "gallery-voting") . '</a>';
		     array_unshift($links, $curLink);
		     return $links;
		}
		
		function post_gallery($output = null, $atts = null, $content = false, $tag = false) {			
			return $output;
		}
		
		function gallery_style($style = null) {

			return $style;
		}
		
		function wp_enqueue_scripts() {
			wp_enqueue_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', false);
		}
		
		function wp_head() {
			echo '<style type="text/css">';
			echo stripslashes(get_option('gallery_voting_css'));
			echo '</style>';
            $adminUrl =  admin_url('admin-ajax.php');
            echo <<<SCRIPT
  <script>
     var galleryvotingajaxurl = "{$adminUrl}";
  </script>
SCRIPT;

		}
		
		function vote() {		
			global $wpdb;
			
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$max_all = get_option('gallery_voting_max_all');
			$max_same = get_option('gallery_voting_max_same');
			$tracking = get_option('gallery_voting_tracking');
			
			$error = false;
			$success = false;
            $dateTime = date("Y-m-d H:i:s");
			if (!empty($_POST)) {
				if (!empty($_POST['attachment_id'])) {
                    $pageId = $_POST['page_id'] ?? 0;
                    $pageUrl = $_POST['page_url'] ?? '';
					$attachment_id = $_POST['attachment_id'] ?? 0;
                    $sourceUrl= $_POST['source_url'] ?? '';
					
					switch ($tracking) {
						case 'cookie'			:
                            $voteAllCookieName = 'gallery_voting_all_' .$pageId;
                            $voteCountSameCookieName = 'gallery_voting_same_' .$pageId . '_' . $attachment_id;
							$voteCount =  $_COOKIE[$voteAllCookieName] ?? 0;
							$voteCountSame = $_COOKIE[$voteCountSameCookieName] ?? 0;
							
							if (empty($voteCount) || $voteCount < $max_all) {

								if (empty($voteCountSame) || $voteCountSame < $max_same) {
									$dbQuery = "INSERT INTO `{$wpdb -> prefix}galleryvotes` (`page_id`,`type`, `ip_address`, `source_url`, `attachment_id`, `email`, `comment`, `page_url`, `created`, `modified`)"
									."VALUES ('{$pageId}',0, '{$ip_address}', '{$sourceUrl}', '{$attachment_id}', '', '', '{$pageUrl}', '{$dateTime}', '{$dateTime}');";
									if ($wpdb -> query($dbQuery)) {
										$success = true;
										setcookie($voteAllCookieName, ($voteCount + 1), (time() + 60 * 60 * 24 * 30));
										setcookie($voteCountSameCookieName, ($voteCountSame + 1), (time() + 60 * 60 * 24 * 30));
									} else {
										$error = "Database could not be updated";
									}
								} else {
									if ($max_same == 1) {
										$error = "You have voted for it already";
									} else {
										$error = sprintf("You have already voted %s times for it", $max_same);
									}
								}
							} else {
								$error = sprintf("You have already voted %s times", $max_all);
							}
							break;
						case 'ipaddress'		:
						default					:
							$votecountquery = "SELECT COUNT(`id`) FROM {$wpdb -> prefix}galleryvotes WHERE `page_id` = '{$pageId}' and type=0 and `ip_address` = '{$ip_address}'";
							$votecount = $wpdb -> get_var($votecountquery);
							if (empty($votecount) || $votecount < $max_all) {
								//same vote?
								$votecountsamequery = "SELECT COUNT(`id`) FROM {$wpdb -> prefix}galleryvotes WHERE `page_id` = '{$pageId}' and type=0 and `ip_address` = '" . $ip_address . "' AND `attachment_id` = '" . $attachment_id . "'";
								$votecountsame = $wpdb -> get_var($votecountsamequery);
								
								if (empty($votecountsame) || $votecountsame < $max_same) {
									$query = "INSERT INTO {$wpdb -> prefix}galleryvotes(`page_id`,`type`,`ip_address`, `source_url`, `attachment_id`, `email`, `comment`, `page_url`, `created`, `modified`)"
									. "VALUES ('{$pageId}', 0, '{$ip_address}', '{$sourceUrl}', '{$attachment_id}', '', '', '{$pageUrl}', '{$dateTime}', '{$dateTime}');";
								    if ($wpdb -> query($query)) {
										$success = true;
									} else {
										$error = "Database could not be updated";
									}
								} else {
									if ($max_same == 1) {
										$error = "You have voted for it already";
									} else {
										$error = sprintf("You have already voted %s times for it", $max_same);
									}
								}
							} else {
								$error = sprintf("You have already voted %s times", $max_all);
							}	
							break;
					}
				} else {
					$error = "No photo was specified";
				}
			} else {
				$error = "No data was posted";
			}
			
			$countQuerySql = "SELECT COUNT(`id`) FROM `{$wpdb -> prefix}galleryvotes` WHERE page_id='{$pageId}' and type=0 and `attachment_id` = '{$attachment_id}'";
			$count = $wpdb -> get_var($countQuerySql);
			if (empty($error)) {
				$data = array(
					'success'		=>	true,
					'count'			=> $count,
				);
			} else {
				$data = array(
					'success'		=>	false,
					'error'			=>	$error,
					'count'			=>	$count,
				);
			}
			header("Content-Type: application/json");
			echo json_encode($data);
			exit();
			die();
		}

        function cus_comment() {
            global $wpdb;
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $max_all = get_option('gallery_comment_max_all') ?? 3;
            $max_same = get_option('gallery_comment_max_same') ?? 1;
            $error = false;
            $success = false;

            if (!empty($_POST)) {
                if (!empty($_POST['attachment_id'])) {
                   $pageId = $_POST['page_id'] ?? 0;
                   $attachment_id = $_POST['attachment_id'] ?? 0;
                   $sourceUrl= $_POST['source_url'] ?? '';
                   $pageUrl = $_POST['page_url'] ?? '';
                   $postEmail = $_POST['email'] ?? '';
                   $postComment = $_POST['comment'] ?? '';
                   $commentQuery = "SELECT COUNT(`id`) FROM {$wpdb -> prefix}galleryvotes WHERE `page_id` = '{$pageId}' and type=1 and auth_type=0 and `ip_address` = '{$ip_address}'";
                   $commentCount = $wpdb -> get_var($commentQuery);

                   if (empty($commentCount) || $commentCount < $max_all) {
                         //same quote?
                       $commentCountSameQuery = "SELECT  COUNT(`id`) FROM {$wpdb -> prefix}galleryvotes WHERE `page_id` = '{$pageId}' and type=1 and auth_type=0 and `ip_address` = '{$ip_address}' AND `attachment_id` = '{$attachment_id}'";
                       $commentCountSame = $wpdb -> get_var($commentCountSameQuery);
                       if (empty($commentCountSame) || $commentCountSame < $max_same) {
                           $date = date("Y-m-d H:i:s");
                           $query = "INSERT INTO {$wpdb -> prefix}galleryvotes(`page_id`, `type`,`ip_address`, `source_url`, `attachment_id`, `email`, `comment`, `page_url`, `auth_type`, `created`, `modified`)"
								." VALUES ({$pageId}, 1, '{$ip_address}', '{$sourceUrl}', '{$attachment_id}', '{$postEmail}', '{$postComment}', '{$pageUrl}', 0, '{$date}', '{$date}');";
                           if ($wpdb->query($query)) {
                               $success = true;
                           } else {
                               $error = "Database could not be updated";
                           }
                       } else {
                           if ($max_same == 1) {
                               $error = "You have commented for it already";
                           } else {
                               $error = sprintf("You have already voted %s times for it", $max_same);
                           }
                       }
                   } else {
                        $error = sprintf("You have already voted %s times for this page", $max_all);
                   }
                } else {
                    $error = "No data was specified";
                }
            } else {
                $error = "No data was posted";
            }
            if (empty($error)) {
                $rows = $this->getCommentFromDb($wpdb, $pageId, $attachment_id);
                $lstHtml = $this->getCommentList($rows);
                $data = array(
                    'success'		=>	true,
                    'html'			=> $lstHtml,
                    'page_id'       => $pageId,
                    'attachment_id' => $attachment_id
                );
            } else {
                $data = array(
                    'success'		=>	false,
                    'error'			=>	$error,
                    'html'			=>	'',
                    'page_id'       => $pageId,
                    'attachment_id' => $attachment_id
                );
            }

            header("Content-Type: application/json");
            echo json_encode($data);

            exit();
            die();
        }
		
		function admin_menu() {
			add_options_page("Gallery Voting", "Gallery Voting", "manage_options", "gallery-voting", array($this, 'admin'));
		}
		
		function admin() {
			if (!empty($_POST)) {
				foreach ($_POST as $pkey => $pval) {
                    if ( str_contains($pkey, 'comment_')) {
                        update_option('gallery_' . $pkey, $pval);
                    } else {
                        update_option('gallery_voting_' . $pkey, $pval);
                    }
				}
				
			}
			$max_all = get_option('gallery_voting_max_all');
			$max_same = get_option('gallery_voting_max_same');
			$tracking = get_option('gallery_voting_tracking');

            $max_comment_all = get_option('gallery_comment_max_all');
            $max_comment_same = get_option('gallery_comment_max_same');
			?>
			
			<div class="wrap gallery-voting">
				<h2>Gallery Voting Settings</h2>
				<form action="" method="post">
					<table class="form-table">
						<tbody>
							<tr>
								<th><label for="max_all">Max Votes Overall</label></th>
								<td>
									<input type="text" name="max_all" value="<?php echo esc_attr(stripslashes($max_all)); ?>" id="max_all" class="widefat" style="width:45px;" /> 
								</td>
							</tr>
							<tr>
								<th><label for="max_same">Max Votes Per Photo</label></th>
								<td>
									<input type="text" name="max_same" value="<?php echo esc_attr(stripslashes($max_same)); ?>" id="max_same" class="widefat" style="width:45px;" />
								</td>
							</tr>
                            <tr>
                                <th><label for="tracking_ipaddress">Tracking</label></th>
                                <td>
                                    <label><input <?php echo (!empty($tracking) && $tracking == "cookie") ? 'checked="checked"' : ''; ?> type="radio" name="tracking" value="cookie" id="tracking_cookie" /> Cookie</label>
                                    <label><input <?php echo (!empty($tracking) && $tracking == "ipaddress") ? 'checked="checked"' : ''; ?> type="radio" name="tracking" value="ipaddress" id="tracking_ipaddress" /> IP Address</label>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="comment_max_all">Max Comments Overall</label></th>
                                <td>
                                    <input type="text" name="comment_max_all" value="<?php echo esc_attr(stripslashes($max_comment_all)); ?>" id="comment_max_all" class="widefat" style="width:45px;" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="comment_max_same">Max Comments Per Photo</label></th>
                                <td>
                                    <input type="text" name="comment_max_same" value="<?php echo esc_attr(stripslashes(($max_comment_same))); ?>" id="comment_max_same" class="widefat" style="width:45px;" />
                                </td>
                            </tr>
							<tr>
								<th><label>Custom CSS</label></th>
								<td>
									<textarea class="widefat" cols="100%" rows="10" name="css"><?php echo stripslashes(get_option('gallery_voting_css')); ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				
					<p class="submit">
						<input type="submit" name="save" value="Save Settings" class="button button-primary" />
					</p>
				</form>
			</div>
			
			<?php
		}


        function get_catfolders_attachments($attachments_data, $posts)
        {

            $ver = '1.0.0.6';
            $plugin_url = plugin_dir_url(__FILE__);
            wp_enqueue_style('cusGalleryStyle',  home_url() . '/wp-includes/blocks/gallery/style.min.css', false);
            wp_enqueue_style('catfolderGalleryStyle',  $plugin_url . 'style/catfolderimg.css' ,  array(), $ver);
            wp_enqueue_script('catfolders-script', $plugin_url . 'js/catfolders-script.js' , array('jquery'), $ver, true);

            $newAttachments = [];
            foreach ( $posts as $innerImgPost ) {
                $attachment_data = ['id' => $innerImgPost->ID];
                if (!$innerImgPost->guid) {
                    continue;
                }
                $fileUrl = strtolower($innerImgPost->guid);
                if (wp_attachment_is_image( $innerImgPost )) {
                    $imageAlt               = get_post_meta( $innerImgPost->ID, '_wp_attachment_image_alt', true );
                    $imageAlt               = empty( $imageAlt ) ? $innerImgPost->post_title : $imageAlt;
                    $attachment_data['alt'] = $imageAlt;
                    if (strstr($imageAlt, '-[hide]')) {
                        continue;
                    }
                    $imageSrc               = wp_get_attachment_image_src( $innerImgPost->ID, 'full' );
                    $imageSrc               = $imageSrc[0];
                    $attachment_data['src'] = $imageSrc;
                    $imageCaption               = wp_get_attachment_caption( $innerImgPost->ID );
                    $attachment_data['caption'] = $imageCaption;
                    $attachment_data['is_video'] = false;
                } elseif (strstr($fileUrl, '.mp4') || strstr($fileUrl, '.mov')) {
                    $attachment_data['src'] = $innerImgPost->guid;
                    $attachment_data['alt'] = '';
                    $attachment_data['caption'] = '';
                    $attachment_data['is_video'] = true;
                    $thumb_id = get_post_thumbnail_id($innerImgPost->ID );

                    if (!empty( $thumb_id ) ) {
                        $attachment_data['poster'] = wp_get_attachment_url($thumb_id) ?? '';
                    }
                } else {
                    continue;
                }
                $newAttachments[$innerImgPost->ID] = $attachment_data;
            }
            return $this->appendVoteAndComment($newAttachments);
        }

        function appendVoteAndComment($newAttachments)
        {
            global $wpdb, $post;

            $pageId = $post->ID;
            $arrDataId = array_keys($newAttachments);
            //get all vote Num
            $arrVoteNumRet = $this->getInitVoteCommentNum($wpdb, $pageId, 0, $arrDataId, false);
            $arrSameIPCommentCount = $this->getInitVoteCommentNum($wpdb, $pageId, 1, $arrDataId, true);
            $maxCommentAll = get_option('gallery_comment_max_all') ?? 3;
            $maxCommentSame = get_option('gallery_comment_max_same') ?? 1;
            $totalIPCount = array_sum(array_values($arrSameIPCommentCount));

            $pluginUrl = plugins_url();
            foreach ($arrDataId as $dataId) {
                $sourceUrl = $newAttachments[$dataId]['src'] ?? '';
                $voteCount = $arrVoteNumRet[$dataId] ?? 0;
                $sameIPCount = $arrSameIPCommentCount[$dataId] ?? 0;
                $sCreateFormStyle = <<<HTML
<div  class="commentSub commentSub-{$dataId}" style="display:none;">
    <input name="attachment_id" type="hidden" value="{$dataId}"/>
    <input name="source_url" type="hidden" value="{$sourceUrl}"/>
	<div class="cofield cofield-email" >
	      <p class="fieldPar">
              <label class="cus_field_label" for="your-email-{$dataId}">Email</label>
              <input class="grdflex" id="your-email-{$dataId}" autocomplete="email" aria-required="true" aria-invalid="false" value="" type="email" name="your-email" >            
	       </p>
	 </div>	
	<div class="cofield cofield-comment">
	      <p class="fieldPar">
             <label class="cus_field_label" for="your-message-{$dataId}" style="vertical-align: top;display: inline-block;">Comment</label>
             <textarea class="grdflex" id="your-message-{$dataId}" rows="5" aria-invalid="false" name="your-message"></textarea> 
	      </p>
	</div>
	<p><input class="cusbtnSubComent" type="button" value="Submit"  onclick="gallery_voting_comment('{$pageId}', '{$dataId}', '{$sourceUrl}');"><input class="cusbtnCancelComent" type="button" value="Cancel" onclick="gallery_cancel_comment('{$pageId}', '{$dataId}');"></p>
</div>
HTML;

                $arrCommentRows = $this->getCommentFromDb($wpdb, $pageId, $dataId);
                $commentHtml = $this->getCommentList($arrCommentRows);

                $voteCommentHtml = <<<VOTELINK
     <p>
        <a class="votelink" href="#" onclick="gallery_voting_vote('{$pageId}', '{$dataId}', '{$sourceUrl}');return false;">
             <span id="gallery-voting-count-{$dataId}">{$voteCount}</span>
             <i class="fa fa-thumbs-o-up"></i>
             <span style="display:none;margin-left: 10px;vertical-align: middle;margin-bottom: 3px;" id="gallery-voting-loading-{$dataId}">
                 <img style="border:none;" src="{$pluginUrl}/gallery-voting-master/loading.gif" alt="loading" />
             </span>
       </a>
       <span class="spanComment spanComment-{$dataId}" style="float: right;"  data-total-comment-limit="{$maxCommentAll}" data-same-comment-limit="{$maxCommentSame}" data-ip-comments="{$totalIPCount}" onclick="onCommentForm('{$pageId}', '{$dataId}', '{$sourceUrl}');">
           <img loading="lazy" decoding="async" data-id="{$dataId}" src="{$pluginUrl}/gallery-voting-master/comment_icon.png" alt=""/>
       </span>
     </p>
     <div class="wpd-comment-list-{$dataId}" data-sameip="{$sameIPCount}">
          {$commentHtml}
     </div>
     {$sCreateFormStyle}
VOTELINK;

                $newAttachments[$dataId]['vote_comments'] = $voteCommentHtml;
            }
            // 使用usort和array_values以按值降序排序
            uasort($arrVoteNumRet, function($a, $b) {
                return $b - $a;
            });
            //
            $arrSortedLinks = [];
            foreach ($arrVoteNumRet as $k => $v) {
                if (isset($newAttachments[$k])) {
                    //append to result
                    $arrSortedLinks[$k] = $newAttachments[$k];
                    unset($newAttachments[$k]);
                }
            }
            //return sorted link
            return array_merge($arrSortedLinks, $newAttachments);
        }

        function catfolders_generate_html($html, $attachments)
        {
            $html    .= '<figure class="wp-block-gallery has-nested-images columns-default is-cropped wp-block-gallery-3 is-layout-flex wp-block-gallery-is-layout-flex">';
            //begin scott
            foreach ( $attachments as $attachment ) {
                $fileUrl = strtolower($attachment['src']);
                if (strstr($fileUrl, '.mp4') || strstr($fileUrl, '.mov')) {
                    $img =  '<video class="cusMedia" controls="" src="' . esc_attr($attachment['src']) . '" data-id="' . $attachment['id'] .'"></video>';
                    if (!empty( $attachment['poster'])) {
                        $img =  '<video class="cusMedia" controls="" src="' . esc_attr($attachment['src']) . '" data-id="'
                            . $attachment['id'] .'" poster="'. $attachment['poster'] .'"></video>';
                    }
                } else {
                    $img = '<img class="cusMedia" src="' . esc_attr($attachment['src']) . '" alt="' . esc_attr($attachment['alt']) . '" data-id="' . $attachment['id'] . '">';
                }

                $li      = '<figure class="wp-block-image size-large">';
                $li     .= $img;
                if (isset($attachment['vote_comments'])) {
                    $li     .= $attachment['vote_comments'];
                }
                $li .= '</figure>';
                $html .= $li;
            }
            $html .= '</figure>';
            return $html;
        }

        function cusAdminUrl($url, $path, $blog_id, $scheme )
        {
            if ($path == 'media-new.php') {
                if (!empty($_GET['catf']) && !str_contains($url, '?')) {
                    $url .= '?catf=' . $_GET['catf'];
                }
            }
            return $url;
        }
	}
	
	$plugin_file = plugin_basename(__FILE__);
	$GalleryVoting = new GalleryVoting();
	add_action('plugins_loaded', array($GalleryVoting, 'plugins_loaded'), 1, 1);
	add_filter('plugin_action_links_' . $plugin_file, array($GalleryVoting, 'plugin_action_links'), 10, 1);
	add_filter('post_gallery', array($GalleryVoting, 'post_gallery'), 10, 4);
	add_filter('gallery_style', array($GalleryVoting, 'gallery_style'), 10, 1);
	add_action('wp_enqueue_scripts', array($GalleryVoting, 'wp_enqueue_scripts'), 10, 1);
	add_action('wp_head', array($GalleryVoting, 'wp_head'), 10, 1);
	add_action('admin_menu', array($GalleryVoting, 'admin_menu'), 10, 1);

	add_action('wp_ajax_galleryvotingvote', array($GalleryVoting, 'vote'), 10, 1);
	add_action('wp_ajax_nopriv_galleryvotingvote', array($GalleryVoting, 'vote'), 10, 1);
    add_action('wp_ajax_galleryvotingcomment', array($GalleryVoting, 'cus_comment'), 10, 1);
    add_action('wp_ajax_nopriv_galleryvotingcomment', array($GalleryVoting, 'cus_comment'), 10, 1);

    add_filter('catfolders_custom_attachments_data', array($GalleryVoting, 'get_catfolders_attachments'), 10, 2);
    add_filter('cat_custome_generate_html', array($GalleryVoting, 'catfolders_generate_html'), 10, 2);
    add_filter('admin_url', array($GalleryVoting, 'cusAdminUrl'), 10, 4);

}

?>