#!/usr/bin/env bash

PREFIX=${1:-/usr/local}
BIN="$PREFIX/bin"

if [ ! -d "$PREFIX" ]; then
    echo "Directory $PREFIX does not exist!"
    exit
fi

SOURCE=`pwd`
DESTINATION="$PREFIX/cli-dom"

echo "Install $SOURCE to $DESTINATION ... [OK]"
rm -rf "$DESTINATION"
rm -f "$BIN/cli-dom"

if [ -d "$DESTINATION" ]; then
    echo "Destination ($DESTINATION) already exists!"
    exit
fi

cp -r "$SOURCE" "$DESTINATION"
ln -s "$DESTINATION/cli-dom" "$BIN/cli-dom"

echo "Finalize ... [OK]"

chmod -R 755 "$DESTINATION/cli-dom"
chmod 755 "$BIN/cli-dom"
