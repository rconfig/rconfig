<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final class StoreSettingsBannerDTO extends DtoBase
{
    public string $login_banner;

    public function __construct(array $parameters = [])
    {
        $this->login_banner = $parameters['login_banner'];
    }
}
