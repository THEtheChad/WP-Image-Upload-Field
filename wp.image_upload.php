<?php

add_action('admin_menu', 'add_meta_boxes');
function add_meta_boxes(){
    add_meta_box( 'meta_id', 'META TITLE', 'display_meta', 'page', 'advanced', 'high', array('test') );
}

function display_meta($post, $meta){
    $images = get_post_meta($post->ID, 'image_list', true);

    echo '<ul class="image_list">';

    if($images)
        foreach($images as $url){
            echo "<li style='background-image:url({$url})'><input name='image_url[]' class='image_url' type='text' value='{$url}'/>";
        }

    echo "<li><input class='image_upload button-primary' type='submit' value='Upload'/><input name='image_url[]' class='image_url' type='text'/>";

    echo '</ul><br style="clear:both">';
}



add_action('save_post', 'meta_box_save');
function meta_box_save($post_id){
   
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    update_post_meta($post_id, 'image_list', $_POST['image_url']);
}


add_action('admin_head', 'ui_scripts');
function ui_scripts(){
?>
<script>
(function($){
    $(function(){
        var $image_lists = $('ul.image_list'),
            idx = $image_lists.length,
            image_template = "<li style='background-image:url({$url})'><input name='image_url[]' class='image_url' type='text' value='{$url}' style='display:none'/><div class='delete_image'>X</div";

        $image_lists.sortable();

        while(idx){
            $list = $image_lists.eq(--idx);
            
            $list.find('li').not(':last').append('<div class="delete_image">X</div>');
            
            $list.find('input.image_url').hide().last().remove();
        
            $list.delegate('div.delete_image', 'click', function(e){
                $(this).parent().remove();
            });
            
            $list.delegate('input.image_upload', 'click', function(e){
                
                tb_show('', 'media-upload.php?TB_iframe=true');
                window.send_to_editor = function(html){
                    var url = $(html).attr('href'),
                        new_image = image_template.replace(/\{\$url\}/g, url);

                    $list.prepend(new_image);

                    tb_remove();
                };
                
                e.preventDefault();
            });
        }
    });
})(jQuery);
</script>
<style>
.image_list li{
position: relative;
background: #e2e2e2;
border: 5px solid #e2e2e2;
height: 100px;
width: 100px;
text-align: center;
float: left;
margin: 0 10px 10px 0;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
}
.image_list li:hover .delete_image{
display: block;
}
.image_upload{
margin-top: 34px;
}
.image_url{
position: absolute;
width: 100%;
margin: 0;
bottom: -1px;
left: 0;
}
.delete_image{
display: none;
position: absolute;
top: -9px;
right: -9px;
background: #777;
border-radius: 50%;
width: 17px;
height: 14px;
font: bold 11px Arial;
color: white;
padding-top: 2px;
border: 2px solid #888;
}
</style>
<?php
}