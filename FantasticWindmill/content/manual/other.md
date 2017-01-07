[User Manual](index.html)

# Other features

- [Passing command-line parameters](#cli)
- [Customizing the web console](#custom-ui)
- [Checking the environment](#environment)

## <a name="cli">Passing command-line parameters</a>

It may be desirable to make the lab depend on settings that are decided by the user when it is launched. For example, in our running example comparing sorting algorithms, one could wish to have control on the increment step for the size of the array. Our [quick tutorial](quick-tutorial.html) had a hard-coded increment step of 100 elements.

It is possible for a lab to declare command-line arguments, that the lab can then retrieve in its `setup` method. To this end, one must implement a method called `setupCli`. When called, this method is given an instance of a [CliParser](/doc/ca/uqac/lif/labpal/CliParser.html) object, which will be used to parse the command-line arguments. Method `setupCli` can add new arguments to this parser. For example, let us add an argument called `step`, which will define the increment step to use when creating the experiments:

<pre><code>public void setupCli(CliParser parser) {
  parser.add(new Argument().withLongName("step").withArgument("n")
    .withDescription("Increment array sizes by n"));
}
</code>
</pre>

To retrieve the value of `step` when the lab is setup, one can call `getCliArguments`:

<pre><code>public void setup() {
  int step = 100;
  ArgumentMap map = getCliArguments();
  if (map.hasOption("step")) {
    step = Integer.parseInt(map.getOptionValue("step"));
  }
  ... (Rest of setup code)
}
</code>
</pre>

In the above snippet, the value of `step` is set to 100; if a command line argument has been given, then `step` is replaced by the value passed at the command line. This can be done when starting the lab:

    $ java -jar my-lab.jar --step 200

Please refer to the API documentation of [CliParser](/doc/ca/uqac/lif/labpal/CliParser.html) for precisions on its usage.

## <a name="custom-ui">Customizing the web console</a>

LabPal's web console can be customized to some extent. One needs to add method `setupCallbacks` to the lab. This method must return a collection (typically a list or a set) of objects of type [WebCallback](/doc/ca/uqac/lif/labpal/server/WebCallback.html). A web callback is an object that is called when a given URL is requested to the server. It has a method called `process`, which creates an HTTP response from the request. As an example, here is a simple callback that prints a dummy HTML page:

<pre><code>public class HelloCallback extends WebCallback {
  public HelloCallback(Laboratory lab, LabAssistant assistant) {
    super("/hello", lab, assistant);
  }
  public CallbackResponse process(HttpExchange t) {
    CallbackResponse r = new CallbackResponse(t);
    response.setContentType(ContentType.HTML);
    response.setCode(CallbackResponse.HTTP_OK);
    response.setContents("&lt;html&gt;&lt;body&gt;Hello world!&lt;/body&gt;&lt;/html&gt;");
    return response;
  }
}
</code>
</pre>

Once this callback is defined, the lab can register it in `setupCallbacks`:

<pre><code>public Collection<WebCallback> setupCallbacks(Laboratory lab, LabAssistant assistant) {
  List<WebCallback> list = new ArrayList<WebCallback>();
  list.add(new HelloCallback(lab, assistant));
  return list;
}
</code>
</pre>

When started, the web console now has a new page, `http://localhost:21212/hello` which, when called, returns the "Hello world" page defined in the corresponding callback.

## <a name="environment">Checking the environment</a>

LabPal and its [template project](https://github.com/liflab/labpal-project) encourage the production of a single, stand-alone, runnable environment that includes all its necessary libraries and input files. This way, a lab can easily be copied around and run without any setup.

There exist situations, however, where this is not possible, and the lab has requirements with respect to the environment in which it is meant to be executed. For example, your experiments may require the use of an external database, some minimum amount of memory or disk space, or some other program that needs to be installed and called from inside the experiments. If these requirements are not met, the lab cannot run or produce meaningful results.

It is possible to verify these conditions at startup by adding to the lab a method called `isEnvironmentOk`. Insert there any code that can check that the conditions for running the lab are met. For example, the following method checks if a program called "foo" is installed by attempting to run it from the command line:

<pre><code>public String isEnvironmentOk() {
  CommandRunner runner = new CommandRunner(new String[]{"foo"});
  runner.run();
  if (runner.getExitCode() != 0)
    return "Command foo cannot be called at the command line";
  return null;
}
</code>
</pre>

An error is detected by checking that the exit code is different from 0. (See [CommandRunner](/doc/ca/uqac/lif/labpal/CommandRunner.html).)

You must return null if everything is OK. As a rule, returning a non-null value (typically an error message) means that something *external* to the lab must be fixed before running the experiments; hence LabPal will simply quit. If your experiments have [prerequisites](experiments.html#prerequisites) they can generate by themselves, don't use this method.

<!-- :wrap=soft:mode=markdown: -->