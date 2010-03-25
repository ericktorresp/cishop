from django.db import models
from django.contrib.auth.models import User
from mysite.lotteries.models import Lottery, Method, Issue
from records.models import Order

class Task(models.Model):
    title = models.CharField(max_length=255)
    user = models.ForeignKey(User, related_name='task_user')
    lottery = models.ForeignKey(Lottery, related_name='lottery')
    method = models.ForeignKey(Method, related_name='method')
    order = models.ForeignKey(Order, related_name='order')
    code = models.TextField()
    code_type = models.CharField(max_length=30, choices=(('input', 'input'), ('digital', 'digital'), ('dxds', 'dxds')))
    total_issues = models.SmallIntegerField(max_length=2)
    finished_issues = models.SmallIntegerField(max_length=2)
    canceled_issues = models.SmallIntegerField(max_length=2)
    price = models.DecimalField(decimal_places=4, max_digits=14)
    total_amount = models.DecimalField(decimal_places=4, max_digits=14)
    finished_amount = models.DecimalField(decimal_places=4, max_digits=14)
    canceled_amount = models.DecimalField(decimal_places=4, max_digits=14)
    start_time = models.DateTimeField(editable=False, auto_now_add=True)
    start_issue = models.CharField(max_length=30)
    win_issues = models.SmallIntegerField(max_length=2)
    update_time = models.DateTimeField(auto_now=True, editable=False)
    prize = models.TextField()
    diffpoints = models.TextField()
    supperior = models.ForeignKey(User, related_name='supperior', null=True, blank=True, editable=False)
    supperior_point = models.DecimalField(decimal_places=3, max_digits=4)
    status = models.SmallIntegerField(max_length=1, choices=((0, 'inprogress'), (1, 'canceled'), (2, 'finished')))
    stop_on_win = models.BooleanField()
    client_ip = models.IPAddressField(db_index=True)
    proxy_ip = models.IPAddressField()

    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'records'
