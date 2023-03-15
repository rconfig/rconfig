<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreSettingsBannerDTO extends DataTransferObject
{
    public string $login_banner;
}
