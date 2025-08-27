FROM nextcloud:apache

ENV DEBIAN_FRONTEND=noninteractive

# Install build tools and dependencies
RUN apt update && apt install -y \
    git \
    build-essential \
    autoconf \
    libtool \
    pkg-config \
    libjpeg-dev \
    libpng-dev \
    libtiff-dev \
    libx11-dev \
    libxml2-dev \
    libheif-dev \
    libde265-dev \
    libwebp-dev \
    libraw-dev \
    ca-certificates \
#    php-imagick \
    nano \
    wget

# Install ImageMagick from source with HEIC support
RUN cd /usr/local/src && \
    git clone https://github.com/ImageMagick/ImageMagick.git && \
    cd ImageMagick && \
    git checkout $(git tag | sort -V | grep -E '^7\.' | tail -n1) && \
    ./configure --with-heic=yes && \
    make -j$(nproc) && \
    make install && \
    ldconfig

# Verify HEIC support
RUN convert -list format | grep HEIC || echo "⚠️ HEIC not supported"

# Enable PHP imagick extension (in case not enabled)
RUN docker-php-ext-enable imagick || true

