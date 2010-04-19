from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug
from django.contrib.auth.models import User

class Business(models.Model):
    title = models.CharField(max_length=100)
    photo = FileBrowseField(u'Photo', max_length=200, directory="business/", format="Image", extensions=['.jpg', '.gif', '.png'])
    max_vistors = models.SmallIntegerField()
    price = models.IntegerField()
    expend = models.IntegerField()
    type = models.CharField(max_length=20, choices=(('bar', 'bar'), ('club', 'club')))
    limit = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    user_business = models.ManyToManyField(User, through='UserBusiness')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id']

class UserBusiness(models.Model):
    user = models.ForeignKey(User)
    business = models.ForeignKey(Business)
    title = models.CharField(max_length=200, blank=True, null=True)
    description = models.CharField(max_length=255, blank=True, null=True)
    max_respect = models.SmallIntegerField()
    entrance_fee = models.SmallIntegerField()
    created = models.DateTimeField(editable=False, auto_now_add=True)
    modified = models.DateTimeField(editable=False, auto_now=True)
    income = models.IntegerField()
    closed = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        db_table = 'user_business'
        app_label = 'system'
