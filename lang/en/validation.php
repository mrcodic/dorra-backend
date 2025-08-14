<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',
    'phone' => 'The :attribute field must be a valid number.',
    'password' => [
        'min' => 'Your password must be at least 8 characters long, contain uppercase and lowercase letters, at least one number, and one special symbol. Example: MyP@ssw0rd!',
        'letters' => 'Your password must be at least 8 characters long, contain uppercase and lowercase letters, at least one number, and one special symbol. Example: MyP@ssw0rd!',
        'mixed' => 'Your password must be at least 8 characters long, contain uppercase and lowercase letters, at least one number, and one special symbol. Example: MyP@ssw0rd!',
        'numbers' => 'Your password must be at least 8 characters long, contain uppercase and lowercase letters, at least one number, and one special symbol. Example: MyP@ssw0rd!',
        'symbols' => 'Your password must be at least 8 characters long, contain uppercase and lowercase letters, at least one number, and one special symbol. Example: MyP@ssw0rd!',
    ],



    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'colors.*' => [
            'required' => 'Please make sure you specify all colors or at least one.',
        ],
        'designs.*' => [
            'exists' => 'The selected design does not exist in your account or is invalid.',

        ],
        'emails.*' => [
            'exists' => 'The selected emails is invalid.',
            'required' => 'The selected email is required.',
            'email' => 'The selected email is invalid format.',
        ],
        'addresses.*.label' => [
            'required' => 'Each address must have a label (e.g., Home, Work).',
            'min' => 'The address label must be at least :min characters.',
        ],
        'addresses.*.line' => [
            'required' => 'Each address must have a street or line value.',
            'min' => 'The address line must be at least :min characters.',
        ],
        'addresses.*.state_id' => [
            'required' => 'Each address must have a valid state selected.',
            'exists' => 'The selected state is not valid.',
        ],
        'name.en' => [
            'required' => 'The English name is required.',
            'string' => 'The English name must be a valid string.',
            'max' => 'The English name must not exceed 255 characters.',
            'unique' => 'This English name already exists.',
        ],
        'name.ar' => [
            'required' => 'The Arabic name is required.',
            'string' => 'The Arabic name must be a valid string.',
            'max' => 'The Arabic name must not exceed 255 characters.',
            'unique' => 'This Arabic name already exists.',
        ],
        'description.en' => [
            'nullable' => 'The English description is optional.',
            'string' => 'The English description must be a valid string.',
        ],
        'description.ar' => [
            'nullable' => 'The Arabic description is optional.',
            'string' => 'The Arabic description must be a valid string.',
        ],
        'image' => [
            'required' => 'Please upload an image.',
            'image' => 'The uploaded file must be an image.',
        ],
        'parent_id' => [
            'required' => 'The main category is required.',
            'integer' => 'You must select a valid main category.',
            'exists' => 'The selected main category does not exist.',
        ],
        'product_id' => [
            'required' => 'The product is required.',
            'integer' => 'You must select a valid product.',
            'exists' => 'The selected product does not exist.',
        ],
        'role_id' => [
            'required' => 'The role is required.',
            'integer' => 'You must select a valid role.',
            'exists' => 'The selected role does not exist.',
        ],
        'images' => [
            'array' => 'The product images must be an array.',
        ],
        'images.*' => [
            'image' => 'Each product image must be a valid image.',
            'mimes' => 'Each image must be of type: jpg, jpeg, png.',
        ],
        'category_id' => [
            'required' => 'Please select a category.',
            'integer' => 'You must select a valid category.',
            'exists' => 'The selected category does not exist.',
        ],
        'sub_category_id' => [
            'integer' => 'You must select a valid subcategory.',
            'exists' => 'The selected subcategory does not exist.',
        ],
        'tags' => [
            'array' => 'Tags must be provided as an array.',
        ],
        'has_custom_prices' => [
            'required' => 'Please specify whether the product has custom pricing.',
            'boolean' => 'Invalid value for custom pricing option.',
        ],
        'base_price' => [
            'required_if' => 'Base price is required if custom pricing is not enabled.',
            'prohibited_if' => 'Base price must not be set if custom pricing is enabled.',
            'numeric' => 'Base price must be a numeric value.',
            'min' => 'Base price must be at least 0.',
        ],
        'prices' => [
            'required_if' => 'Prices are required if custom pricing is enabled.',
            'prohibited_if' => 'Prices must not be set if custom pricing is disabled.',
            'array' => 'Prices must be an array.',
        ],
        'prices.*.quantity' => [
            'required' => 'Each custom price tier must include a quantity.',
            'integer' => 'Quantity must be an integer.',
            'min' => 'Quantity must be at least 0.',
        ],
        'prices.*.price' => [
            'required' => 'Each custom price tier must include a price.',
            'integer' => 'Price must be an integer.',
            'min' => 'Price must be at least 0.',
        ],
        'specifications' => [
            'required' => 'Product specifications are required.',
            'array' => 'Specifications must be an array.',
        ],
        'specifications.*.name_en' => [
            'nullable' => 'English name of specification is optional.',
            'string' => 'English name of specification must be a valid string.',
            'max' => 'English name of specification must not exceed 255 characters.',
        ],
        'specifications.*.name_ar' => [
            'nullable' => 'Arabic name of specification is optional.',
            'string' => 'Arabic name of specification must be a valid string.',
            'max' => 'Arabic name of specification must not exceed 255 characters.',
        ],
        'specifications.*.specification_options' => [
            'required' => 'Each specification must have at least one option.',
            'array' => 'Specification options must be an array.',
            'min' => 'Each specification must have at least one option.',
        ],
        'specifications.*.specification_options.*.value_en' => [
            'required' => 'English value of the specification option is required.',
            'string' => 'English value must be a valid string.',
            'max' => 'English value must not exceed 255 characters.',
        ],
        'specifications.*.specification_options.*.value_ar' => [
            'required' => 'Arabic value of the specification option is required.',
            'string' => 'Arabic value must be a valid string.',
            'max' => 'Arabic value must not exceed 255 characters.',
        ],
        'specifications.*.specification_options.*.price' => [
            'nullable' => 'Option price is optional.',
            'numeric' => 'Option price must be numeric.',
            'min' => 'Option price must be at least 0.',
        ],
        'specifications.*.specification_options.*.image' => [
            'nullable' => 'Option image is optional.',
            'image' => 'Option image must be an image.',
            'mimes' => 'Option image must be of type: jpg, jpeg, png.',
        ],
        'is_free_shipping' => [
            'boolean' => 'Invalid value for free shipping option.',
        ],
        'status' => [
            'in' => 'The selected status is invalid.',
        ],
        'state_id' => [
            "required" => "The selected state is required.",
            "integer" => "The selected state is invalid.",
        ]
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
