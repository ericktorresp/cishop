#!/usr/bin/env python

import os, sys

sys.path.append(os.path.abspath('..'))
os.environ['DJANGO_SETTINGS_MODULE'] = 'settings'
from channels.models import Channel
#
print Channel.objects.count()
