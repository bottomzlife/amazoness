#!/bin/bash
POTDIR="languages"
for i in $(find $POTDIR -type f -name "*.po")
do
    STEM=$(basename -s ".po" $i)
    msgfmt -o $POTDIR/$STEM.mo $POTDIR/$STEM.po
done

