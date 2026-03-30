#!/bin/bash

# RenderingVideo PHP SDK Release Script
# Usage: ./release.sh <version>

set -e

VERSION=$1

if [ -z "$VERSION" ]; then
    echo "Usage: ./release.sh <version>"
    echo "Example: ./release.sh 1.0.2"
    exit 1
fi

# Validate version format
if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: Version must be in format x.y.z (e.g., 1.0.2)"
    exit 1
fi

echo "=== Releasing version $VERSION ==="

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "Error: You have uncommitted changes. Please commit first."
    exit 1
fi

# Update CHANGELOG
echo "Updating CHANGELOG.md..."
sed -i "s/## \[Unreleased\]/## [Unreleased]\n\n## [$VERSION] - $(date +%Y-%m-%d)/" CHANGELOG.md

# Commit changelog update
git add CHANGELOG.md
git commit -m "chore: update changelog for v$VERSION"

# Create git tag
echo "Creating git tag v$VERSION..."
git tag -a "v$VERSION" -m "Release v$VERSION"

# Push to origin
echo "Pushing to origin..."
git push origin main
git push origin "v$VERSION"

echo ""
echo "=== Release v$VERSION created successfully! ==="
echo ""
echo "Packagist will automatically update if webhook is configured."
echo "If not, visit: https://packagist.org/packages/renderingvideo/sdk"
echo ""
echo "Install with: composer require renderingvideo/sdk"
