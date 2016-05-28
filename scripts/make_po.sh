#!/bin/bash
LNG="ja"
BASE="languages/$(basename $(pwd))"
INPUT="$BASE.pot"
OUTPUT="$BASE-$LNG.po"
[[ -f "$OUTPUT" ]] \
    && cp "$OUTPUT" "$OUTPUT.save"
msginit \
    --no-translator \
    --locale="$LNG" \
    --input="$INPUT" \
    --output="$OUTPUT"

