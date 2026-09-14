# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## 1.1.0 (unreleased)

- Add device-bound AgentAuth with Ed25519 proof, token exchange/refresh, and context/audit operations; ordinary sk- keys remain supported.
- Add API capability discovery and title/category support, including category=all for website tasks.
- Preserve preview metadata and URL fields; encode identifiers and surface API errors.
- Add an enhanced schema example and cross-language API/device-proof contract checks.

New capability/category features require the corresponding website API update. This source version does not imply a published package or production deployment.

## [1.0.0] - 2026-03-27

### Added
- Initial release
- Video resource with full CRUD operations
  - Create video tasks
  - List video tasks with pagination and filtering
  - Get task details
  - Delete tasks
  - Trigger rendering
  - Wait for completion helper
- File resource for asset management
  - Upload single or multiple files
  - Upload from content/string
  - List files with pagination and filtering
  - Delete files
- Preview resource for temporary previews
  - Create preview links
  - Get preview configuration
  - Delete previews
  - Convert to permanent task
  - Convert and render immediately
- Credits resource
  - Get balance
  - Calculate render cost
  - Check affordability
- Comprehensive exception handling
  - ApiException (base)
  - AuthenticationException
  - ValidationException
  - NotFoundException
  - InsufficientCreditsException
  - AlreadyRenderingException
- Response models with helper methods
  - VideoTask
  - VideoTaskList
  - RenderResult
  - UploadResult
  - UploadedFile
  - FileList
  - PreviewTask
  - CreditsBalance
- Unit tests
- Full documentation
