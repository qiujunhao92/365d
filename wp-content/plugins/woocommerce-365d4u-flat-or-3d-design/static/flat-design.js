jQuery(document).ready(function($) {
    function setupMediaUploader(buttonSelector, gallerySelector, inputSelector, title, buttonText, isVideo = false) {
        let fileFrame;

        $(buttonSelector).on('click', function(e) {
            e.preventDefault();

            // Reopen existing frame if it exists
            if (fileFrame) {
                fileFrame.open();
                return;
            }

            // Create new media frame
            fileFrame = wp.media.frames.fileFrame = wp.media({
                title: title,
                button: { text: buttonText },
                multiple: true
            });

            // Handle file selection
            fileFrame.on('select', function() {
                const attachmentIds = [];
                const attachments = fileFrame.state().get('selection').map(function(attachment) {
                    attachment = attachment.toJSON();
                    attachmentIds.push(attachment.id);
                    return attachment.id;
                });

                // Clear and append selected files to gallery
                $(gallerySelector).html('');
                $.each(attachmentIds, function(index, id) {
                    const fileUrl = wp.media.attachment(id).get('url');
                    if (isVideo) {
                        $(gallerySelector).append('<video width="150" height="100" controls><source src="' + fileUrl + '" type="video/mp4"></video>');
                    } else {
                        $(gallerySelector).append('<img src="' + fileUrl + '" width="100" height="100" style="margin-right: 10px;">');
                    }
                });

                // Set hidden input value
                $(inputSelector).val(attachmentIds.join(','));
            });

            fileFrame.open();
        });
    }

    // Setup media uploaders for Flat, 3D, and Production Design
    // 确保按钮 ID 和展示区 ID 与 display_media_fields 中生成的一致
    setupMediaUploader('#upload_flat_design_images_button', '#flat_design_images_gallery', '#flat_design_images', 'Select Flat Design Images', 'Add Images');
    setupMediaUploader('#upload_flat_design_videos_button', '#flat_design_videos_gallery', '#flat_design_videos', 'Select Flat Design Videos', 'Add Videos', true);
    setupMediaUploader('#upload_3d_design_images_button', '#3d_design_images_gallery', '#3d_design_images', 'Select 3D Design Images', 'Add Images');
    setupMediaUploader('#upload_3d_design_videos_button', '#3d_design_videos_gallery', '#3d_design_videos', 'Select 3D Design Videos', 'Add Videos', true);
    setupMediaUploader('#upload_production_images_button', '#production_images_gallery', '#production_images', 'Select Production Images', 'Add Images');
    setupMediaUploader('#upload_production_videos_button', '#production_videos_gallery', '#production_videos', 'Select Production Videos', 'Add Videos', true);
});
