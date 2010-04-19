from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User

class Armor(models.Model):
    title = models.CharField(max_length=100)
    tolerance = models.IntegerField(max_length=6)
    price = models.IntegerField()
    photo = FileBrowseField(u'Image', max_length=200, directory="armor/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    user_armor = models.ManyToManyField(User, through='UserArmor')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'

class UserArmor(models.Model):
    user = models.ForeignKey(User)
    armor = models.ForeignKey(Armor)
    actived = models.BooleanField(default=False)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username + '\'s' + self.armor.title
    
    class Meta:
        db_table = 'user_armor'
        app_label = 'system'
