#!/bin/sh

chmod 700 /etc/tor/torrc
chown tor:root /etc/tor/torrc
chmod 700 /var/lib/tor
chown -R tor:root /var/lib/tor

runuser -u tor -- /usr/bin/tor -f /etc/tor/torrc