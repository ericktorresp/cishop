# coding=utf-8
from games.models import *
from django.contrib import admin

from home.widgets import ImageWidget

class GameAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'photo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(GameAdmin,self).formfield_for_dbfield(db_field, **kwargs)
    
    list_display = ('display_name', 'url_name', 'url', )
    
admin.site.register(Game, GameAdmin)