#!/usr/bin/env python3
"""
Utility script used by EgoNextApp to pre-generate the PNG previews Nextcloud
expects for HEIC files.

Usage:
    python3 heic_previews.py /path/to/file.heic /path/to/preview/dir

The script relies on ImageMagick (`magick` or `convert`) being available in
PATH. Preview sizes can be overridden through the NEXTCLOUD_PREVIEW_SIZES
environment variable (comma separated WxH pairs, e.g. "32x32,256x256").
"""

from __future__ import annotations

import argparse
import os
import pathlib
import shutil
import subprocess
import sys
from typing import Iterable, List, Sequence, Tuple

DEFAULT_SIZES: Tuple[Tuple[int, int], ...] = (
    (32, 32),
    (64, 64),
    (128, 128),
    (256, 256),
    (512, 512),
    (1024, 1024),
)


class PreviewGenerationError(RuntimeError):
    """Raised when a preview cannot be generated."""


def parse_sizes(env_value: str | None) -> Tuple[Tuple[int, int], ...]:
    if not env_value:
        return DEFAULT_SIZES
    sizes: List[Tuple[int, int]] = []
    for chunk in env_value.split(","):
        chunk = chunk.strip()
        if not chunk:
            continue
        try:
            w_str, h_str = chunk.lower().split("x", 1)
            width = int(w_str)
            height = int(h_str)
            sizes.append((width, height))
        except (ValueError, TypeError) as exc:
            raise ValueError(
                f"Invalid size '{chunk}'. Expected format WIDTHxHEIGHT."
            ) from exc
    if not sizes:
        raise ValueError("No valid preview sizes specified.")
    return tuple(sizes)


def find_imagemagick_binary() -> str:
    for candidate in ("magick", "convert"):
        path = shutil.which(candidate)
        if path:
            return path
    raise PreviewGenerationError(
        "ImageMagick binary not found. Install 'magick' or 'convert' and "
        "ensure it is available in PATH."
    )


def build_command(
    binary: str,
    src: pathlib.Path,
    dest: pathlib.Path,
    width: int,
    height: int,
) -> Sequence[str]:
    cmd = [binary]
    # ImageMagick 7 encourages invoking "magick input output".
    # For ImageMagick 6 the binary is convert, so we just reuse the same CLI.
    cmd.extend(
        [
            str(src),
            "-auto-orient",
            "-resize",
            f"{width}x{height}^",
            "-gravity",
            "center",
            "-extent",
            f"{width}x{height}",
            str(dest),
        ]
    )
    return cmd


def generate_preview(
    binary: str,
    src: pathlib.Path,
    dest_dir: pathlib.Path,
    size: Tuple[int, int],
) -> pathlib.Path:
    width, height = size
    dest_dir.mkdir(parents=True, exist_ok=True)
    dest = dest_dir / f"{width}-{height}.png"
    cmd = build_command(binary, src, dest, width, height)
    result = subprocess.run(
        cmd,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        check=False,
        text=True,
    )
    if result.returncode != 0:
        raise PreviewGenerationError(
            f"Failed to generate {dest.name}: {result.stderr.strip()}"
        )
    return dest


def ensure_path_exists(path: pathlib.Path) -> None:
    if not path.exists():
        raise FileNotFoundError(f"Path not found: {path}")
    if not path.is_file():
        raise FileNotFoundError(f"Expected a file: {path}")


def run(heic_path: pathlib.Path, previews_base: pathlib.Path) -> None:
    ensure_path_exists(heic_path)
    sizes = parse_sizes(os.getenv("NEXTCLOUD_PREVIEW_SIZES"))
    binary = find_imagemagick_binary()

    generated: List[pathlib.Path] = []
    for size in sizes:
        generated.append(generate_preview(binary, heic_path, previews_base, size))

    for path in generated:
        print(path)


def main(argv: Sequence[str] | None = None) -> int:
    parser = argparse.ArgumentParser(
        description="Generate Nextcloud-ready PNG previews from a HEIC file."
    )
    parser.add_argument("heic_path", help="Path to the source HEIC file")
    parser.add_argument(
        "preview_dir",
        help="Directory to store the generated Nextcloud previews "
        "(files will be named <width>-<height>.png)",
    )
    args = parser.parse_args(argv)

    heic_path = pathlib.Path(args.heic_path).expanduser().resolve()
    preview_dir = pathlib.Path(args.preview_dir).expanduser().resolve()

    try:
        run(heic_path, preview_dir)
    except (PreviewGenerationError, FileNotFoundError, ValueError) as exc:
        print(f"[heic_previews] {exc}", file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
