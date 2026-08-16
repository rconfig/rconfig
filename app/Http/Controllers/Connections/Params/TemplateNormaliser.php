<?php

namespace App\Http\Controllers\Connections\Params;

use Psr\Log\LoggerInterface;

/**
 * Canonicalises template values before anything downstream consumes them.
 *
 * Symfony's YAML parser types `on`, `off`, `yes` and `no` as plain strings but types
 * `true` and `false` as real booleans, so a template author writing the intuitive
 * thing produced a value no consumer recognised. The consumers then disagreed with
 * each other: `auth.enable` was compared loosely and happened to survive the mistake,
 * `config.paging` was compared strictly and silently stopped disabling the pager. Same
 * mistake, opposite outcomes, nothing logged either way.
 *
 * Every switch is settled on 'on' or 'off' here, and `connect.protocol` is lowercased,
 * so the consumers compare one canonical form.
 */
class TemplateNormaliser
{
    /**
     * Switch keys per template section, canonicalised to 'on' or 'off'.
     *
     * @var array<string, list<string>>
     */
    private const BOOLEAN_KEYS = [
        'connect' => ['isNonInteractiveMode'],
        'auth' => ['enable', 'enableUsername', 'hpAnyKeyStatus', 'sshInteractive'],
        'config' => ['paging', 'isMikrotik'],
        'options' => ['AnsiHost'],
        'vt100' => ['hasSplashScreen', 'hasSplashScreenEnterKey'],
    ];

    /**
     * @var list<string>
     */
    private const TRUTHY = ['on', 'yes', 'true', 'y', '1', 'enable', 'enabled'];

    /**
     * @var list<string>
     */
    private const FALSY = ['off', 'no', 'false', 'n', '0', '', 'disable', 'disabled'];

    public function __construct(private LoggerInterface $logger) {}

    /**
     * @param  array<string, mixed>|null  $template
     * @return array<string, mixed>|null
     */
    public function normalise(?array $template): ?array
    {
        if ($template === null) {
            return null;
        }

        $template = $this->normaliseProtocol($template);

        foreach (self::BOOLEAN_KEYS as $section => $keys) {
            if (! isset($template[$section]) || ! is_array($template[$section])) {
                continue;
            }

            foreach ($keys as $key) {
                if (! array_key_exists($key, $template[$section])) {
                    continue;
                }

                $template[$section][$key] = $this->toSwitch($template[$section][$key], $section . '.' . $key);
            }
        }

        return $template;
    }

    /**
     * The dispatcher compares against lowercase literals, so `protocol: SSH` used to
     * fall through to the generic "your template file could be invalid" exception with
     * no pointer at the capital letters. Lowercase and trim once, here.
     *
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private function normaliseProtocol(array $template): array
    {
        if (! isset($template['connect']) || ! is_array($template['connect'])) {
            return $template;
        }

        if (! array_key_exists('protocol', $template['connect']) || ! is_string($template['connect']['protocol'])) {
            return $template;
        }

        $raw = $template['connect']['protocol'];
        $protocol = strtolower(trim($raw));

        if ($protocol !== $raw) {
            $this->logger->info('Template value normalised', [
                'key' => 'connect.protocol',
                'from' => $raw,
                'to' => $protocol,
            ]);
        }

        $template['connect']['protocol'] = $protocol;

        return $template;
    }

    /**
     * A missing value stays missing, since the consumers treat null as "not set".
     * A value nobody recognises is left exactly as written and logged, so it keeps its
     * current off-by-default behaviour rather than being guessed at.
     */
    private function toSwitch(mixed $value, string $path): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $this->logNormalised($path, $value ? 'true' : 'false', $value ? 'on' : 'off');
        }

        if (! is_scalar($value)) {
            return $this->logUnrecognised($path, $value);
        }

        $candidate = strtolower(trim((string) $value));

        if (in_array($candidate, self::TRUTHY, true)) {
            return $candidate === 'on' ? 'on' : $this->logNormalised($path, (string) $value, 'on');
        }

        if (in_array($candidate, self::FALSY, true)) {
            return $candidate === 'off' ? 'off' : $this->logNormalised($path, (string) $value, 'off');
        }

        return $this->logUnrecognised($path, $value);
    }

    private function logNormalised(string $path, string $from, string $to): string
    {
        $this->logger->info('Template value normalised', [
            'key' => $path,
            'from' => $from,
            'to' => $to,
        ]);

        return $to;
    }

    private function logUnrecognised(string $path, mixed $value): mixed
    {
        $this->logger->warning('Template value is not a recognised on/off switch and was left as written', [
            'key' => $path,
            'value' => is_scalar($value) ? (string) $value : gettype($value),
        ]);

        return $value;
    }
}
