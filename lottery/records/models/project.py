from django.db import models
from django.contrib.auth.models import User
from mysite.lotteries.models import Lottery, Method, Issue, Mode
from records.models import Order, Task

class Project(models.Model):
    user = models.ForeignKey(User, related_name='user', db_index=True)
    order = models.ForeignKey(Order, related_name='order')
    task = models.ForeignKey(Task, related_name='task', null=True, blank=True, db_index=True)
    lottery = models.ForeignKey(Lottery, related_name='lottery')
    method = models.ForeignKey(Method, related_name='method')
    issue = models.ForeignKey(Issue, to_field='title', max_length=30, db_column='issue', db_index=True)
    bonus = models.DecimalField(decimal_places=4, max_digits=14)
    code = models.TextField(db_index=True)
    code_type = models.CharField(max_length=30, choices=(('input', 'input'), ('digital', 'digital'), ('dxds', 'dxds')))
    price = models.DecimalField(decimal_places=4, max_digits=14)
    mode = models.ForeignKey(Mode, db_column='mode', to_field='rate')
    multiple = models.IntegerField(max_length=8)
    total_amount = models.DecimalField(decimal_places=4, max_digits=14)
    supperior = models.ForeignKey(User, related_name='project_supperior', null=True, blank=True, editable=False, db_index=True)
    supperior_point = models.DecimalField(decimal_places=3, max_digits=4)
    created = models.DateTimeField(auto_now_add=True, editable=False)
    update_time = models.DateTimeField(auto_now=True, editable=False)
    is_deducted = models.BooleanField(default=False, editable=False)
    is_canceled = models.SmallIntegerField(default=0, editable=False, max_length=1, db_index=True)
    is_get_prize = models.SmallIntegerField(default=0, editable=False, max_length=1, db_index=True)
    is_send_prize = models.BooleanField(default=False, editable=False)
    client_ip = models.IPAddressField(db_index=True)
    proxy_ip = models.IPAddressField()
    db_queries = models.SmallIntegerField(max_length=7)
    hash = models.CharField(max_length=32)
    
    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'records'
