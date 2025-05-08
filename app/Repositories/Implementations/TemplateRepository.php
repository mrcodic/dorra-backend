<?php

namespace App\Repositories\Implementations;

use App\Models\Template;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\TemplateRepositoryInterface;

class TemplateRepository extends BaseRepository implements TemplateRepositoryInterface
{
    public function __construct(Template $template)
    {
        parent::__construct($template);
    }

}
