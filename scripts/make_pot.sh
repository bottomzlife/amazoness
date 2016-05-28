#!/bin/bash
xgettext \
    -k"gg" -k"__" -k"_e" \
    -o languages/$(basename $(pwd)).pot \
    --from-code=utf8 \
    $(find -type f -name '*.php')
