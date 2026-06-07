<?php

return [
    'tree' => [
        'empty_label' => 'لا توجد حسابات',
    ],

    'action' => [
        'create_child_node' => 'إضافة حساب فرعي',

        'delete_failed_title' => 'فشل الحذف',
        'delete_failed_body_has_child' => 'يوجد حسابات فرعية، يرجى حذفها أولاً.',

        'move_node' => 'نقل الحساب',
        'move_node_success' => 'تم نقل الحساب بنجاح',
        'move_node_failed' => 'فشل نقل الحساب',
        'move_node_failed_body_depth' => 'لا يمكن تجاوز المستوى المسموح (:level).',

        'fix_nestedset' => 'إصلاح الشجرة',
        'fix_nestedset_success' => 'تم إصلاح الشجرة بنجاح',
    ],

    'field' => [
        'parent_select_field' => 'الحساب الأب',
        'parent_select_field_placeholder' => 'اختر الحساب الأب',
        'parent_select_field_empty_label' => 'لا توجد حسابات أب',
    ],
];
