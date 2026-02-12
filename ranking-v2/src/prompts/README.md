# Prompts Directory

This directory contains all AI prompts used by the system. These prompts can be easily shared with colleagues and modified without touching the PHP code.

## File Structure

### Metadata Extraction
- `metadata_extraction.txt` - Main prompt for extracting metadata from URLs
- `metadata_extraction_system.txt` - System message for metadata extraction

### Failure Explanation
- `failure_explanation.txt` - Prompt for explaining why metadata extraction failed
- `failure_explanation_system.txt` - System message for failure explanation

### Summary Creation
- `summary_creation.txt` - Prompt for creating summaries
- `summary_creation_system.txt` - System message for summary creation

### Relevance Rating
- `relevance_rating.txt` - Prompt for AI relevance rating (1-5 scale)
- `relevance_rating_system.txt` - System message for relevance rating

## Placeholders

The prompts use placeholders that are replaced at runtime:

- `{URL}` - The URL being processed
- `{DOMAIN}` - The domain of the URL
- `{PATH}` - The path of the URL
- `{CONTENT_SECTION}` - The content section (varies by prompt)
- `{EXISTING_CONTENT_TYPES_SECTION}` - List of existing content types (for metadata extraction)
- `{ERROR_MESSAGE}` - Error message (for failure explanation)
- `{METADATA_SECTION}` - Existing metadata (for summary creation)
- `{TITLE}` - Page title (for relevance rating)
- `{META_DESCRIPTION}` - Meta description (for relevance rating)
- `{TEXT}` - Extracted text (for relevance rating)

## How to Modify

1. Edit the `.txt` files directly
2. The PHP code will automatically use the updated prompts
3. No need to modify PHP code when changing prompts
4. Share these files with colleagues for review and collaboration

## Notes

- All prompts are UTF-8 encoded
- Hebrew prompts are written in Hebrew
- English prompts are written in English
- Placeholders are case-sensitive and must match exactly




