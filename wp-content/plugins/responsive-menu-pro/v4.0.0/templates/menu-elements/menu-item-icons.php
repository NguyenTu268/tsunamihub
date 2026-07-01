<?php

$menu_items = rmp_get_wp_nav_menu_items( $options );
$items = [];

if ( ! empty( $menu_items ) ) {
    foreach ( $menu_items as $item ) {
        $items[ $item->ID ] = $item->title;
    }
}

if ( empty( $options['menu_font_icons'] ) ) {

    echo '<div class="rmp-menu-item-icon-container">';

    echo $ui_manager->start_group_controls();
        echo $control_manager->add_select_control( [
            'label'   => __('Select Item','responsive-menu-pro'),
            'id'      => 'rmp-menu-icon-on-menu-item',
            'class'   => 'rmp-menu-icon-on-menu-item',
            'name'    => 'menu[menu_font_icons][id][]',
            'options' => $items,
            'value'   => '',
        ] );

        echo $control_manager->add_icon_picker_control( [
            'id'           => 'rmp-menu-item-font-icon',
            'picker_class' => 'rmp-menu-item-font-icon-picker-button',
            'picker_id'    => "rmp-menu-item-font-icon-selector",
            'name'         => 'menu[menu_font_icons][icon][]',
            'value'        => '',
        ] );
    echo $ui_manager->end_group_controls();

    echo $control_manager->rmp_textarea_control_html( [
        'label'    => __('Custom Icon Code','responsive-menu-pro'),
        'id'       => 'rmp-menu-item-custom-font-icon',
        'name'     => 'menu[menu_font_icons][custom_icon][]',
        'value'    => '',
        'tool_tip' => array(
            'text' => esc_html__( 'Use custom icon code or SVG code.', 'responsive-menu' ),
        ),

    ] );

    echo '</div>';

} else {
    if ( ! empty($options['menu_font_icons']['id']) && is_array( $options['menu_font_icons']['id'] ) ) {
        $count = 0;
        foreach ( $options['menu_font_icons']['id'] as $key => $item_id ) {
            $count++;

            echo '<div class="rmp-menu-item-icon-container">';
            echo $ui_manager->start_group_controls();

            echo $control_manager->add_select_control( [
                'label'         => __('Select Item','responsive-menu-pro'),
                'id'            => 'rmp-menu-icon-on-menu-item',
                'class'         => 'rmp-menu-icon-on-menu-item',
                'group_classes' => 'full-size',
                'name'          => 'menu[menu_font_icons][id][]',
                'options'       => $items,
                'value'         => $item_id,
            ] );

            echo $control_manager->add_icon_picker_control( [
                'id'            => 'rmp-menu-item-font-icon-' . $key,
                'group_classes' => 'full-size',
                'picker_class'  => 'rmp-menu-item-font-icon-picker-button',
                'picker_id'     => "rmp-menu-item-font-icon-selector-". $key,
                'name'          => 'menu[menu_font_icons][icon][]',
                'value'         => $options['menu_font_icons']['icon'][ $key ],
            ] );

            if ( 1 !== $count ) {
                echo '<span class="delete-menu-item-icon dashicons dashicons-no"></span>';
            }

            echo $ui_manager->end_group_controls();
            echo $control_manager->rmp_textarea_control_html( [
                'label'    => __('Custom Icon Code','responsive-menu-pro'),
                'id'       => 'rmp-menu-item-custom-font-icon-' . $key,
                'name'     => 'menu[menu_font_icons][custom_icon][]',
                'value'    => '',
                'tool_tip' => array(
                    'text' => esc_html__( 'Use custom icon code or SVG code.', 'responsive-menu' ),
                ),
                'value'    => ! empty( $options['menu_font_icons']['custom_icon'][ $key ] ) ? $options['menu_font_icons']['custom_icon'][ $key ] : "",
            ] );
            echo '</div>';
        }
    }
}

echo $control_manager->add_button_control(
    [
        'label' => __('Add Icon','responsive-menu-pro'),
        'id'    => 'rmp-menu-add-item-icon',
        'class' => '',
    ]
);
