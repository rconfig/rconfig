<?php

test('basic command functionality', function () {
    $this->artisan('help')->expectsOutput('Description:')->expectsOutput('Arguments:')->assertExitCode(0);
});
