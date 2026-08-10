# Security Policy

rConfig manages credentials for production network infrastructure, so we take reports about this codebase seriously and we would rather hear about a problem early than read about it later.

## Supported versions

Security fixes are issued for the current minor release of rConfig V8 Core. Older releases are not patched, so please upgrade to the latest `core-` tag before reporting an issue you cannot reproduce on a current build.

| Version | Supported |
| ------- | --------- |
| 8.2.x   | Yes       |
| < 8.2   | No        |

## Reporting a vulnerability

Please report privately. Do not open a public GitHub issue, pull request, or discussion for a suspected vulnerability, and please do not post proof of concept details publicly before a fix is available.

Two options:

1. **GitHub Security Advisories (preferred)**: use the "Report a vulnerability" button on the Security tab of this repository. This gives us a private thread with you and a direct route to requesting a CVE.
2. **Email**: security@rconfig.com

## What to include

The more of this you can provide, the faster we can confirm and fix:

- The version or commit you tested against, and how it was deployed.
- A description of the issue and the component or file involved.
- Steps to reproduce, ideally a concrete request or proof of concept.
- What an attacker gains, and what level of access they need to start with.
- Any suggested fix, if you have one.

## What to expect from us

- **Acknowledgement within 3 working days** of your report.
- **An initial assessment within 10 working days**, including whether we have reproduced the issue and our view of the severity.
- Regular updates while we work on a fix, and notice before we publish.
- Credit in the release notes and any advisory, under whatever name and link you prefer. Tell us if you would rather stay anonymous.

## Disclosure

We work to coordinated disclosure. Our aim is to ship a fix and publish an advisory within 90 days of a valid report, and sooner for issues that are straightforward or actively exploited. If we need longer, we will explain why rather than let the clock run quietly. We are happy to agree a joint publication date with you.

If you intend to request a CVE, tell us and we will work with you on it, either through GitHub Security Advisories or with your own CNA.

## Scope

In scope: the code in this repository, including the API, the SPA, the download and connection layer, authentication, and the installation scripts we ship.

Out of scope:

- Findings against rConfig Pro. Those belong on the Pro support channel.
- Issues in third party dependencies with no rConfig specific exploit path. Report those upstream, though we do want to hear if a dependency issue is directly exploitable here.
- Missing hardening headers, missing rate limits, or scanner output with no demonstrated impact.
- Anything requiring physical access to the server, or a pre-existing root or database compromise.
- Social engineering of rConfig staff or users.
- Behaviour that only affects an installation the reporter controls and that a normal deployment does not expose, for example an intentionally misconfigured `.env` or a self-signed certificate warning.

## Safe harbour

We will not pursue or support legal action against anyone who reports a vulnerability in good faith under this policy, who tests only against their own installation, who avoids accessing or modifying other people's data, and who gives us a reasonable opportunity to fix the issue before disclosing it.

## Deployment hardening

If you are securing an rConfig installation rather than reporting a bug, start with the security guidance in the documentation at https://v8coredocs.rconfig.com and make sure `APP_DEBUG` is `false`, `APP_KEY` is unique to your install, and the `storage` and `.env` paths are not served by your web server.
