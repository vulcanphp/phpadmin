<script type="text/javascript">
    window.editor.on('asset:remove', function(asset) {
        let assetId = asset.attributes.public_id,
            token = '<?= csrf_token(); ?>';
        $.ajax({
            type: 'post',
            url: '<?= url()->relativeUrl() ?>',
            data: {
                _phppage_action: 'asset_manager',
                action: 'delete',
                file: assetId,
                _token: token
            }
        });
    });
</script>