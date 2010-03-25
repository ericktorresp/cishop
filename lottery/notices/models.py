from django.db import models
from mysite.channels.models import Channel
from django.contrib.auth.models import User

class Notice(models.Model):
    title = models.CharField(max_length=80)
    content = models.TextField()
    created = models.DateTimeField(editable=False, auto_now_add=True)
    author = models.ForeignKey(User, related_name="author_user", editable=False)
    channel = models.ForeignKey(Channel)
    checker = models.ForeignKey(User, related_name="check_user", editable=False, blank=True, default='0', null=True)
    is_deleted = models.BooleanField(default=False)
    is_top = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.title
