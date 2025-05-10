<?php

return [

    /*
    |----------------------------------------------------------------------
    | Validation Language Lines
    |----------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => ':attribute ليس عنوان URL صالح.',
    'after' => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي :attribute على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون :attribute مصفوفة.',
    'before' => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file' => 'يجب أن يكون :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون :attribute بين :min و :max أحرف.',
        'array' => 'يجب أن يحتوي :attribute على بين :min و :max عناصر.',
    ],
    'boolean' => 'يجب أن يكون حقل :attribute صحيحًا أو خطأ.',
    'confirmed' => 'تأكيد :attribute لا يتطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute ليس تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون :attribute تاريخًا يساوي :date.',
    'date_format' => 'لا يتطابق :attribute مع التنسيق :format.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يكون :attribute مكونًا من :digits رقمًا.',
    'digits_between' => 'يجب أن يكون :attribute بين :min و :max رقمًا.',
    'dimensions' => 'لـ :attribute أبعاد صورة غير صالحة.',
    'distinct' => 'حقل :attribute يحتوي على قيمة مكررة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالح.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum' => ':attribute المختار غير صالح.',
    'exists' => ':attribute المختار غير صالح.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',
    'gt' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'file' => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أكبر من :value أحرف.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'file' => 'يجب أن يكون :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أكبر من أو يساوي :value أحرف.',
        'array' => 'يجب أن يحتوي :attribute على :value عناصر أو أكثر.',
    ],
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => ':attribute المختار غير صالح.',
    'in_array' => 'حقل :attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صالح.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صالح.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صالح.',
    'json' => 'يجب أن يكون :attribute سلسلة JSON صالحة.',
    'lt' => [
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'file' => 'يجب أن يكون :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أقل من :value أحرف.',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'file' => 'يجب أن يكون :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أقل من أو يساوي :value أحرف.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'mac_address' => 'يجب أن يكون :attribute عنوان MAC صالح.',
    'max' => [
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :max.',
        'file' => 'يجب أن يكون :attribute أقل من أو يساوي :max كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أقل من أو يساوي :max أحرف.',
        'array' => 'يجب أن يحتوي :attribute على أقل من أو يساوي :max عناصر.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'min' => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file' => 'يجب أن يكون :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون :attribute على الأقل :min أحرف.',
        'array' => 'يجب أن يحتوي :attribute على الأقل :min عناصر.',
    ],
    'multiple_of' => 'يجب أن يكون :attribute مضاعفًا لـ :value.',
    'not_in' => ':attribute المختار غير صالح.',
    'not_regex' => 'تنسيق :attribute غير صالح.',
    'numeric' => 'يجب أن يكون :attribute عددًا.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون حقل :attribute موجودًا.',
    'prohibited' => 'حقل :attribute محظور.',
    'prohibited_if' => 'حقل :attribute محظور عندما يكون :other هو :value.',
    'prohibited_unless' => 'حقل :attribute محظور إلا إذا كان :other موجودًا في :values.',
    'prohibits' => 'حقل :attribute يحظر وجود :other.',
    'regex' => 'تنسيق :attribute غير صالح.',
    'required' => 'حقل :attribute مطلوب.',
    'required_array_keys' => 'يجب أن يحتوي حقل :attribute على إدخالات لـ: :values.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_unless' => 'حقل :attribute مطلوب إلا إذا كان :other في :values.',
    'required_with' => 'حقل :attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all' => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => 'حقل :attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق :attribute و :other.',
    'size' => [
        'numeric' => 'يجب أن يكون :attribute :size.',
        'file' => 'يجب أن يكون :attribute :size كيلوبايت.',
        'string' => 'يجب أن يكون :attribute :size أحرف.',
        'array' => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون :attribute سلسلة نصية.',
    'timezone' => 'يجب أن يكون :attribute منطقة زمنية صالحة.',
    'unique' => 'تم أخذ :attribute بالفعل.',
    'uploaded' => 'فشل تحميل :attribute.',
    'url' => 'يجب أن يكون :attribute عنوان URL صالح.',
    'uuid' => 'يجب أن يكون :attribute UUID صالح.',
    'phone' => 'يجب أن يكون :attribute رقمًا صالحًا.',

    /*
    |----------------------------------------------------------------------
    | Custom Validation Language Lines
    |----------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom' => [
        'addresses.*.label' => [
            'required' => 'يجب أن يحتوي كل عنوان على تسمية (مثل: منزل، عمل).',
            'min' => 'يجب أن تكون التسمية في العنوان على الأقل :min حروف.',
        ],
        'addresses.*.line' => [
            'required' => 'يجب أن يحتوي كل عنوان على قيمة شارع أو سطر.',
            'min' => 'يجب أن يحتوي السطر في العنوان على الأقل :min حروف.',
        ],
        'addresses.*.state_id' => [
            'required' => 'يجب أن يحتوي كل عنوان على حالة صالحة مختارة.',
            'exists' => 'الحالة المختارة غير صالحة.',
        ],
        'name.en' => [
            'required' => 'الاسم بالإنجليزية مطلوب.',
            'string'   => 'الاسم بالإنجليزية يجب أن يكون نصًا صالحًا.',
            'max'      => 'الاسم بالإنجليزية يجب ألا يتجاوز 255 حرفًا.',
            'unique'   => 'الاسم بالإنجليزية موجود بالفعل.',
        ],
        'name.ar' => [
            'required' => 'الاسم بالعربية مطلوب.',
            'string'   => 'الاسم بالعربية يجب أن يكون نصًا صالحًا.',
            'max'      => 'الاسم بالعربية يجب ألا يتجاوز 255 حرفًا.',
            'unique'   => 'الاسم بالعربية موجود بالفعل.',
        ],
        'description.en' => [
            'nullable' => 'الوصف باللغة الإنجليزية اختياري.',
            'string'   => 'الوصف باللغة الإنجليزية يجب أن يكون نصًا صالحًا.',
        ],
        'description.ar' => [
            'nullable' => 'الوصف باللغة العربية اختياري.',
            'string'   => 'الوصف باللغة العربية يجب أن يكون نصًا صالحًا.',
        ],
        'image' => [
            'required' => 'يرجى تحميل صورة.',
            'image'    => 'الملف المرفوع يجب أن يكون صورة.',
            'mimes'    => 'الصورة يجب أن تكون من نوع: jpeg، png، jpg، أو svg.',
        ],
        'parent_id' => [
            'required' => 'الفئة الرئيسية مطلوبة.',
            'integer'  => 'يجب اختيار فئة رئيسية صالحة.',
            'exists'   => 'الفئة الرئيسية المختارة غير موجودة.',
        ],
        'images' => [
            'array' => 'يجب أن تكون صور المنتج مصفوفة.',
        ],
        'images.*' => [
            'image' => 'كل صورة منتج يجب أن تكون صورة صالحة.',
            'mimes' => 'يجب أن تكون الصورة من نوع: jpg، jpeg، png.',
        ],
        'category_id' => [
            'required' => 'يرجى اختيار فئة.',
            'integer' => 'يجب اختيار فئة صالحة.',
            'exists' => 'الفئة المختارة غير موجودة.',
        ],
        'sub_category_id' => [
            'integer' => 'يجب اختيار فئة فرعية صالحة.',
            'exists' => 'الفئة الفرعية المختارة غير موجودة.',
        ],
        'tags' => [
            'array' => 'يجب تقديم العلامات كمصفوفة.',
        ],
        'has_custom_prices' => [
            'required' => 'يرجى تحديد ما إذا كان للمنتج تسعير مخصص.',
            'boolean' => 'القيمة غير صحيحة لخيار التسعير المخصص.',
        ],
        'base_price' => [
            'required_if' => 'السعر الأساسي مطلوب إذا لم يتم تمكين التسعير المخصص.',
            'prohibited_if' => 'يجب ألا يتم تعيين السعر الأساسي إذا كان التسعير المخصص مفعلًا.',
            'numeric' => 'يجب أن يكون السعر الأساسي قيمة رقمية.',
            'min' => 'يجب أن يكون السعر الأساسي على الأقل 0.',
        ],
        'prices' => [
            'required_if' => 'الأسعار مطلوبة إذا تم تمكين التسعير المخصص.',
            'prohibited_if' => 'يجب ألا يتم تعيين الأسعار إذا كان التسعير المخصص غير مفعل.',
            'array' => 'يجب أن تكون الأسعار مصفوفة.',
        ],
        'prices.*.quantity' => [
            'required' => 'يجب أن يتضمن كل مستوى تسعير مخصص الكمية.',
            'integer' => 'يجب أن تكون الكمية عدد صحيح.',
            'min' => 'يجب أن تكون الكمية على الأقل 0.',
        ],
        'prices.*.price' => [
            'required' => 'يجب أن يتضمن كل مستوى تسعير مخصص السعر.',
            'integer' => 'يجب أن يكون السعر عدد صحيح.',
            'min' => 'يجب أن يكون السعر على الأقل 0.',
        ],
        'specifications' => [
            'required' => 'مواصفات المنتج مطلوبة.',
            'array' => 'يجب أن تكون المواصفات مصفوفة.',
        ],
        'specifications.*.name_en' => [
            'nullable' => 'اسم المواصفة بالإنجليزية اختياري.',
            'string' => 'اسم المواصفة بالإنجليزية يجب أن يكون نصًا صالحًا.',
            'max' => 'اسم المواصفة بالإنجليزية يجب ألا يتجاوز 255 حرفًا.',
        ],
        'specifications.*.name_ar' => [
            'nullable' => 'اسم المواصفة بالعربية اختياري.',
            'string' => 'اسم المواصفة بالعربية يجب أن يكون نصًا صالحًا.',
            'max' => 'اسم المواصفة بالعربية يجب ألا يتجاوز 255 حرفًا.',
        ],
        'specifications.*.specification_options' => [
            'required' => 'يجب أن تحتوي كل مواصفة على خيار واحد على الأقل.',
            'array' => 'يجب أن تكون خيارات المواصفة مصفوفة.',
            'min' => 'يجب أن تحتوي كل مواصفة على خيار واحد على الأقل.',
        ],
        'specifications.*.specification_options.*.value_en' => [
            'required' => 'القيمة باللغة الإنجليزية لخيار المواصفة مطلوبة.',
            'string' => 'القيمة باللغة الإنجليزية يجب أن تكون نصًا صالحًا.',
            'max' => 'القيمة باللغة الإنجليزية يجب ألا تتجاوز 255 حرفًا.',
        ],
        'specifications.*.specification_options.*.value_ar' => [
            'required' => 'القيمة بالعربية لخيار المواصفة مطلوبة.',
            'string' => 'القيمة بالعربية يجب أن تكون نصًا صالحًا.',
            'max' => 'القيمة بالعربية يجب ألا تتجاوز 255 حرفًا.',
        ],
        'specifications.*.specification_options.*.price' => [
            'nullable' => 'سعر الخيار اختياري.',
            'numeric' => 'سعر الخيار يجب أن يكون رقميًا.',
            'min' => 'سعر الخيار يجب أن يكون على الأقل 0.',
        ],
        'specifications.*.specification_options.*.image' => [
            'nullable' => 'صورة الخيار اختياري.',
            'image' => 'صورة الخيار يجب أن تكون صورة.',
            'mimes' => 'صورة الخيار يجب أن تكون من نوع: jpg، jpeg، png.',
        ],
        'is_free_shipping' => [
            'boolean' => 'القيمة غير صحيحة لخيار الشحن المجاني.',
        ],
        'status' => [
            'in' => 'الحالة المختارة غير صالحة.',
        ],
    ],

    /*
  |--------------------------------------------------------------------------
  | Custom Validation Attributes
  |--------------------------------------------------------------------------
  |
  | The following language lines are used to swap our attribute placeholder
  | with something more reader friendly such as "E-Mail Address" instead
  | of "email". This simply helps us make our message more expressive.
  |
  */

    'attributes' => [],
];
