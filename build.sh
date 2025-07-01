#!/bin/bash
shopt -s nullglob
timestamp=$(date +"%Y%m%d%H%M%S")
zipfile="${timestamp}-build.zip"
zip -r "$zipfile" . \
  -x "$zipfile" \
  -x "*-build.zip" \
  -x ".git/*" \
  -x ".gitignore"
  -x "gm_lead/*" \
  -x ".aidigestignore" \
  -x "codebase.md" \
  -x "gmlead.sql"

