from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User

class Hooker(models.Model):
    title = models.CharField(max_length=100)
    photo = FileBrowseField(u'Photo', max_length=200, directory="hooker/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField()
    expend = models.IntegerField()
    visitprice = models.IntegerField()
    sickprobability = models.DecimalField(decimal_places=4, max_digits=4)
    is_random = models.BooleanField(default=False)
    stamina = models.IntegerField()
    spirit = models.SmallIntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    updated = models.DateTimeField('Updated', editable=False, auto_now=True)
    user_hooker = models.ManyToManyField(User, through='UserHooker')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]

class UserHooker(models.Model):
    user = models.ForeignKey(User)
    hooker = models.ForeignKey(Hooker)
    visitprice = models.SmallIntegerField()
    expend = models.SmallIntegerField()
    income = models.IntegerField()
    freetime = models.DateTimeField(blank=True, null=True)
    
    def __unicode__(self):
        return self.user.username + '\'s ' + self.hooker.title
    
    class Meta:
        db_table = 'user_hooker'
        app_label = 'system'
