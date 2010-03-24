from django.db import models
from django.contrib.auth.models import User
from mysite.channels.models import Channel

class UserFund(models.Model):
    user = models.ForeignKey(User)
    channel = models.ForeignKey(Channel)
    cash_balance = models.DecimalField(decimal_places=4, max_digits=14)
    channel_balance = models.DecimalField(decimal_places=4, max_digits=14)
    available_balance = models.DecimalField(decimal_places=4, max_digits=14)
    hold_balance = models.DecimalField(decimal_places=4, max_digits=14)
    is_locked = models.BooleanField(default=False)
    last_update_time = models.DateTimeField(auto_now=True, editable=False)
    last_active_time = models.DateTimeField(auto_now=True, editable=False)
    
    def __unicode__(self):
        return self.user.username + '\'s Fund'
    
class UserLog(models.Model):
    title = models.CharField(max_length=30)
    content = models.TextField()
    user = models.ForeignKey(User, db_index=True)
    channel = models.ForeignKey(Channel)
    client_ip = models.IPAddressField(editable=False, db_index=True)
    proxy_ip = models.IPAddressField(editable=False, db_index=True)
    created = models.DateTimeField(auto_now_add=True, editable=False, db_index=True)
    query_string = models.TextField()
    controller = models.CharField(max_length=40)
    actioner = models.CharField(max_length=40)
    request_string = models.TextField()
    
    def __unicode__(self):
        return self.title
