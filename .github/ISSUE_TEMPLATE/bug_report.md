name: Bug Report
description: Report a software defect
title: "[Bug]: "
labels: ["bug"]
body:
  - type: input
    attributes:
      label: rConfig version
    validations:
      required: true

  - type: textarea
    attributes:
      label: Steps to reproduce
    validations:
      required: true

  - type: textarea
    attributes:
      label: Expected behaviour
    validations:
      required: true

  - type: textarea
    attributes:
      label: Actual behaviour
    validations:
      required: true
