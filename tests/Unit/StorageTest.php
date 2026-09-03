<?php

test('get rconfig appdir paths', function () {
    $appDirPath = config('rConfig.app_dir_path');

    expect(rconfig_appdir_path())->toEqual($appDirPath);
    expect(rconfig_appdir_storage_path())->toEqual($appDirPath . '/storage');
});
