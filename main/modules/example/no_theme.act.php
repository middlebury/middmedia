<?php

$harmoni = Harmoni::instance();

// These lines are only here because we already attached the GUIManager to
// Harmoni as the output Handler. This is all done within Harmoni by default,
// so if you you comment out GUI Manager startup in the config, any text printed
// in an action will be placed in the <body> tags of an HTML document.
$osidContext = new OsidContext;
$osidContext->assignContext('harmoni', $harmoni);
$configuration = new BasicOutputHandlerConfigProperties;
$outputHandler = new BasicOutputHandler;
$outputHandler->assignOsidContext($osidContext);
$outputHandler->assignConfiguration($configuration);
$outputHandler->attachToHarmoni();

print "\n<p>"._("This action isn't using themes.")."</p>";

print "\n<p><a href='".$harmoni->request->quickURL("example","home")."'>"._("Click here to go Home.")."</a></p>";