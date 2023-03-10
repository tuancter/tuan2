<div class="row">
    <div class="col-12">
        <?php if (!empty($product)):
            if (!empty($productAudio)):?>
                <div class="dm-uploader-container">
                    <div id="drag-and-drop-zone-audio" class="dm-uploader dm-uploader-media text-center">
                        <ul class="dm-uploaded-files dm-uploaded-media-file">
                            <li class="media li-dm-media-preview">
                                <div class="audio-player audio-player-preview w-100">
                                    <a href="javascript:void(0)" class="btn-img-delete btn-video-delete position-relative float-right m-l-5" onclick="deleteProductAudioPreview('<?= $product->id; ?>','<?= trans("confirm_product_audio", true) ?>');">
                                        <i class="icon-close"></i>
                                    </a>
                                    <audio id="audio_player" controls>
                                        <source src="<?= getProductAudioUrl($productAudio); ?>" type="audio/mp3"/>
                                    </audio>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="dm-uploader-container">
                    <div id="drag-and-drop-zone-audio" class="dm-uploader dm-uploader-media text-center">
                        <p class="dm-upload-icon">
                            <i class="icon-upload"></i>
                        </p>
                        <p class="dm-upload-text"><?= trans("drag_drop_file_here"); ?>&nbsp;<span style="text-decoration: underline"><?= trans('browse_files'); ?></span></p>
                        <a class='btn btn-md dm-btn-select-files'>
                            <input type="file" name="file">
                        </a>
                        <ul class="dm-uploaded-files dm-uploaded-media-file" id="files-audio"></ul>
                        <div class="error-message-file-upload">
                            <p class="m-0 text-center"></p>
                        </div>
                    </div>
                </div>
            <?php endif;
        endif; ?>
    </div>
</div>