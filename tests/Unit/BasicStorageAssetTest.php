<?php

test('data dir is present', function () {
    expect(config_data_path())->toBeDirectory();
});
