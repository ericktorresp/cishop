from django.db import models
from django.contrib.auth.models import User
from accounts.models.account_type import AccountType
from mysite.lotteries.models import Lottery, Method, Mode
from mysite.channels.models import Channel

class Account(models.Model):
    title = models.CharField(max_length=40)
    description = models.CharField(max_length=100)
    amount = models.DecimalField(decimal_places=4, max_digits=14)
    lottery = models.ForeignKey(Lottery, blank=True, null=True)
    method = models.ForeignKey(Method, blank=True, null=True)
    order = models.ForeignKey(Order, blank=True, null=True)
    task = models.ForeignKey(Task, blank=True, null=True)
    project = models.ForeignKey(Project, blank=True, null=True)
    mode = models.ForeignKey(Mode, db_column='mode', to_field='rate')
    from_user = models.ForeignKey(User, blank=True, null=True, related_name='from_user')
    to_user = models.ForeignKey(User, blank=True, null=True, related_name='to_user')
    type = models.ForeignKey(AccountType)
    pre_balance = models.DecimalField(decimal_places=4, max_digits=14)
    pre_hold = models.DecimalField(decimal_places=4, max_digits=14)
    pre_available = models.DecimalField(decimal_places=4, max_digits=14)
    suf_balance = models.DecimalField(decimal_places=4, max_digits=14)
    suf_hold = models.DecimalField(decimal_places=4, max_digits=14)
    suf_available = models.DecimalField(decimal_places=4, max_digits=14)
    client_ip = models.IPAddressField(editable=False)
    proxy_ip = models.IPAddressField(editable=False)
    db_time = models.DateTimeField(editable=False, auto_now_add=True)
    action_time = models.DateTimeField(editable=False)
    source_channel = models.ForeignKey(Channel, blank=True, null=True, related_name='source_channel')
    dest_channel = models.ForeignKey(Channel, blank=True, null=True, related_name='dest_channel')
    operator = models.ForeignKey(User, blank=True, null=True, related_name='operator')
    status = models.SmallIntegerField(max_length=1, default=0, choices=((1, 1), (2, 2), (3, 3)), editable=False)
    hashvar = models.CharField(max_length=32, editable=False)
    
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'accounts'
