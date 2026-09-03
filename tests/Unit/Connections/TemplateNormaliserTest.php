<?php

use App\Http\Controllers\Connections\Params\TemplateNormaliser;
use Psr\Log\AbstractLogger;
use Symfony\Component\Yaml\Yaml;

beforeEach(function () {
    $this->logged = [];

    $logger = new class($this->logged) extends AbstractLogger
    {
        /**
         * @param  list<array{level: mixed, message: string|Stringable, context: array<string, mixed>}>  $logged
         */
        public function __construct(private array &$logged) {}

        public function log($level, string|Stringable $message, array $context = []): void
        {
            $this->logged[] = ['level' => $level, 'message' => $message, 'context' => $context];
        }
    };

    $this->normaliser = new TemplateNormaliser($logger);
});

/**
 * @param  list<array{level: mixed, message: string|Stringable, context: array<string, mixed>}>  $logged
 * @return list<array{level: mixed, message: string|Stringable, context: array<string, mixed>}>
 */
function loggedAt(array $logged, string $level): array
{
    return array_values(array_filter($logged, fn (array $entry): bool => $entry['level'] === $level));
}

/**
 * @param  array<string, mixed>|null  $template
 * @return array<string, mixed>
 */
function normaliseTemplate(TemplateNormaliser $normaliser, ?array $template): ?array
{
    return $normaliser->normalise($template) ?? [];
}

test('yaml types on and true differently which is the whole problem', function () {
    $parsed = Yaml::parse("paging: on\nenable: true\nsplash: yes\nquiet: off\n");

    expect($parsed['paging'])->toBe('on');
    expect($parsed['enable'])->toBeTrue();
    expect($parsed['splash'])->toBe('yes');
    expect($parsed['quiet'])->toBe('off');
});

test('boolean true and false become on and off', function () {
    $result = normaliseTemplate($this->normaliser, [
        'auth' => ['enable' => true],
        'config' => ['paging' => false],
    ]);

    expect($result['auth']['enable'])->toBe('on');
    expect($result['config']['paging'])->toBe('off');
});

test('yes and no become on and off', function () {
    $result = normaliseTemplate($this->normaliser, [
        'options' => ['AnsiHost' => 'yes'],
        'config' => ['isMikrotik' => 'no'],
    ]);

    expect($result['options']['AnsiHost'])->toBe('on');
    expect($result['config']['isMikrotik'])->toBe('off');
});

test('canonical values are left alone', function () {
    $result = normaliseTemplate($this->normaliser, [
        'auth' => ['enable' => 'on', 'hpAnyKeyStatus' => 'off'],
    ]);

    expect($result['auth']['enable'])->toBe('on');
    expect($result['auth']['hpAnyKeyStatus'])->toBe('off');
});

test('casing and surrounding whitespace are tolerated', function () {
    $result = normaliseTemplate($this->normaliser, [
        'auth' => ['enable' => ' ON ', 'sshInteractive' => 'TRUE'],
        'vt100' => ['hasSplashScreen' => 'No'],
    ]);

    expect($result['auth']['enable'])->toBe('on');
    expect($result['auth']['sshInteractive'])->toBe('on');
    expect($result['vt100']['hasSplashScreen'])->toBe('off');
});

test('an empty value reads as off', function () {
    $result = normaliseTemplate($this->normaliser, ['config' => ['paging' => '']]);

    expect($result['config']['paging'])->toBe('off');
});

test('a null value stays null', function () {
    $result = normaliseTemplate($this->normaliser, ['config' => ['paging' => null]]);

    expect($result['config']['paging'])->toBeNull();
});

test('absent keys and absent sections stay absent', function () {
    $result = normaliseTemplate($this->normaliser, [
        'auth' => ['enable' => 'on'],
    ]);

    expect($result['auth'])->not->toHaveKey('paging');
    expect($result)->not->toHaveKey('config');
    expect($result)->not->toHaveKey('vt100');
});

test('keys that are not switches are untouched', function () {
    $result = normaliseTemplate($this->normaliser, [
        'config' => ['paging' => 'yes', 'pagingCmd' => 'terminal length 0', 'exitCmd' => 'no paging'],
        'auth' => ['username' => 'Username:', 'enable' => 'off'],
    ]);

    expect($result['config']['pagingCmd'])->toBe('terminal length 0');
    expect($result['config']['exitCmd'])->toBe('no paging');
    expect($result['auth']['username'])->toBe('Username:');
});

test('an unrecognised switch is left as written and logged', function () {
    $result = normaliseTemplate($this->normaliser, ['config' => ['paging' => 'sometimes']]);

    expect($result['config']['paging'])->toBe('sometimes');

    $warnings = loggedAt($this->logged, 'warning');
    expect($warnings)->toHaveCount(1);
    expect($warnings[0]['context']['key'])->toBe('config.paging');
    expect($warnings[0]['context']['value'])->toBe('sometimes');
});

test('a value that had to be rewritten is logged', function () {
    normaliseTemplate($this->normaliser, [
        'connect' => ['protocol' => 'SSH'],
        'auth' => ['enable' => true],
        'config' => ['paging' => 'off'],
    ]);

    $keys = array_column(array_column(loggedAt($this->logged, 'info'), 'context'), 'key');

    expect($keys)->toContain('connect.protocol');
    expect($keys)->toContain('auth.enable');
    expect($keys)->not->toContain('config.paging', 'A value already in canonical form should not be logged.');
});

test('protocol is lowercased and trimmed', function () {
    $result = normaliseTemplate($this->normaliser, ['connect' => ['protocol' => ' SSH ']]);

    expect($result['connect']['protocol'])->toBe('ssh');
});

test('protocol already lowercase is left alone', function () {
    $result = normaliseTemplate($this->normaliser, ['connect' => ['protocol' => 'telnet']]);

    expect($result['connect']['protocol'])->toBe('telnet');
});

test('a null template is returned untouched', function () {
    expect($this->normaliser->normalise(null))->toBeNull();
});

test('a full template normalises every switch it carries', function () {
    $template = Yaml::parse(file_get_contents(__DIR__ . '/../../storage/templates/vt100.yml'));

    $result = normaliseTemplate($this->normaliser, $template);

    expect($result['connect']['protocol'])->toBe('ssh');
    expect($result['auth']['enable'])->toBe('off');
    expect($result['config']['paging'])->toBe('off');
    expect($result['options']['AnsiHost'])->toBe('on');
    expect($result['vt100']['hasSplashScreen'])->toBe('on');
    expect($result['vt100']['hasSplashScreenEnterKey'])->toBe('off');
    expect($result['config']['pagingCmd'])->toBe('terminal length 0');
});
