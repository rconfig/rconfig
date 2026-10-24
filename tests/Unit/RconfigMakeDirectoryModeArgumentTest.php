<?php

use Symfony\Component\Finder\Finder;

/**
 * rconfig_make_directory() chmods every level it creates, so the mode is a
 * security decision. Its 0755 default exists only to match PRO's signature;
 * every CORE call site must pass the mode explicitly.
 */
test('every rconfig_make_directory() call site passes the mode explicitly', function () {
    $offenders = [];
    $callsFound = 0;

    $files = Finder::create()
        ->files()
        ->name('*.php')
        ->in([base_path('app'), base_path('routes'), base_path('database'), base_path('config')]);

    foreach ($files as $file) {
        $tokens = token_get_all($file->getContents());
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING || $tokens[$i][1] !== 'rconfig_make_directory') {
                continue;
            }

            if (makeDirPreviousSignificantToken($tokens, $i) === T_FUNCTION) {
                continue;
            }

            $open = makeDirNextSignificantIndex($tokens, $i);

            if ($open === null || $tokens[$open] !== '(') {
                continue;
            }

            $callsFound++;

            if (makeDirTopLevelArgumentCount($tokens, $open) < 2) {
                $offenders[] = $file->getRelativePathname() . ':' . $tokens[$i][2];
            }
        }
    }

    expect($callsFound)->toBeGreaterThan(0, 'Expected to find rconfig_make_directory() call sites to check.');
    expect($offenders)->toBe([], 'These rconfig_make_directory() calls omit the mode: ' . implode(', ', $offenders));
});

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function makeDirPreviousSignificantToken(array $tokens, int $index): int|string|null
{
    for ($i = $index - 1; $i >= 0; $i--) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i];
    }

    return null;
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function makeDirNextSignificantIndex(array $tokens, int $index): ?int
{
    $count = count($tokens);

    for ($i = $index + 1; $i < $count; $i++) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return $i;
    }

    return null;
}

/**
 * Count the arguments of the call whose opening parenthesis is at $open,
 * ignoring commas nested inside brackets.
 *
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function makeDirTopLevelArgumentCount(array $tokens, int $open): int
{
    $depth = 0;
    $arguments = 0;
    $sawContent = false;
    $count = count($tokens);

    for ($i = $open; $i < $count; $i++) {
        $token = $tokens[$i];
        $text = is_array($token) ? $token[1] : $token;

        if (in_array($text, ['(', '[', '{'], true) || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
            $depth++;

            if ($depth === 1) {
                continue;
            }
        }

        if (in_array($text, [')', ']', '}'], true)) {
            $depth--;

            if ($depth === 0) {
                return $sawContent ? $arguments + 1 : 0;
            }
        }

        if ($depth === 1 && $text === ',') {
            $arguments++;

            continue;
        }

        if ($depth >= 1 && ! (is_array($token) && $token[0] === T_WHITESPACE)) {
            $sawContent = true;
        }
    }

    return $arguments;
}

test('the call site checker counts arguments correctly', function () {
    $countFor = function (string $code): int {
        $tokens = token_get_all('<?php ' . $code);

        foreach ($tokens as $index => $token) {
            if (is_array($token) && $token[1] === 'rconfig_make_directory') {
                return makeDirTopLevelArgumentCount($tokens, makeDirNextSignificantIndex($tokens, $index));
            }
        }

        return -1;
    };

    expect($countFor('rconfig_make_directory($dir);'))->toBe(1);
    expect($countFor('rconfig_make_directory(path_for($a, $b));'))->toBe(1);
    expect($countFor('rconfig_make_directory($dir, 0750);'))->toBe(2);
    expect($countFor('rconfig_make_directory(foo([$a, $b]), (int) config("x", 1));'))->toBe(2);
    expect($countFor('rconfig_make_directory("{$a}/b", 0750);'))->toBe(2);
});
