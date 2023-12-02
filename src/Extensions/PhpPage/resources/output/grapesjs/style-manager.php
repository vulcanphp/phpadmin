<script type="text/javascript">

    let styleManager = editor.StyleManager;

    styleManager.addSector('advanced',{
        name: 'Advanced',
        open: false,
        properties: []
    }, { at: 10 });

    <?php
foreach ([
    'position'   => [
        'width'      => 'Width',
        'min-width'  => 'Minimum width',
        'max-width'  => 'Maximum width',
        'height'     => 'Height',
        'min-height' => 'Minimum height',
        'max-height' => 'Maximum height',
        'padding'    => [
            'name'       => 'Padding',
            'properties' => [
                'padding-top'    => 'Padding top',
                'padding-right'  => 'Padding right',
                'padding-bottom' => 'Padding bottom',
                'padding-left'   => 'Padding left',
            ],
        ],
        'margin'     => [
            'name'       => 'Margin',
            'properties' => [
                'margin-top'    => 'Margin top',
                'margin-right'  => 'Margin right',
                'margin-bottom' => 'Margin bottom',
                'margin-left'   => 'Margin left',
            ],
        ],
        'text-align' => [
            'name' => 'Text align',
        ],
    ],
    'background' => [
        'background-color' => 'Background color',
        'background'       => 'Background',
    ],
] as $sector => $sectorProperties) {
    foreach ($sectorProperties as $property => $data) {
        if (is_array($data)) {
            for ($i = 0; $i < sizeof($data['properties'] ?? []); $i++) {
                $translation = $data['properties'][array_keys($data['properties'])[$i]];
                ?>
    window.editor.StyleManager.getProperty('<?=$sector?>', '<?=$property?>').attributes.properties.models[<?=$i?>].attributes.name = '<?=$translation?>';
    <?php
}
            ?>
    window.editor.StyleManager.getProperty('<?=$sector?>', '<?=$property?>').set({ name: '<?=$data['name']?>' });
    <?php
} else {
            ?>
    window.editor.StyleManager.getProperty('<?=$sector?>', '<?=$property?>').set({ name: '<?=$data?>' });
    <?php
}
    }
}
?>
</script>
