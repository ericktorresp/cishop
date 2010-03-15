from django.db import models
from mysite.channels.models import Channel
from django.contrib.auth.models import User

class Help(models.Model):
    channel = models.ForeignKey(Channel)
    subject = models.CharField(max_length=20, choices=(('bank', 'bank'), ('faq', 'faq')))
    title = models.CharField(max_length=100)
    content = models.TextField()
    author = models.ForeignKey(User, editable=False)
    created = models.DateTimeField(editable=False)
    is_deleted = models.BooleanField('delete?', default=False)
    sort = models.SmallIntegerField(default=0, blank=True)
    
    def __unicode__(self):
        return self.title
