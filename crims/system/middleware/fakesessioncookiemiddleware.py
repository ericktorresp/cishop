
# coding=utf-8
from django.conf import settings

class FakeSessionCookieMiddleware(object):

    def process_request(self, request):
        if not request.COOKIES.has_key(settings.SESSION_COOKIE_NAME):
            if request.POST.has_key(settings.SESSION_COOKIE_NAME):
                request.COOKIES[settings.SESSION_COOKIE_NAME] = request.POST[settings.SESSION_COOKIE_NAME]
            elif request.GET.has_key(settings.SESSION_COOKIE_NAME):
                request.COOKIES[settings.SESSION_COOKIE_NAME] = request.GET[settings.SESSION_COOKIE_NAME]
