[User Manual](index.html)

# Calling external commands

(This page was copied from an old version of LabPal; it requires a bit of cleanup.)

So far, we assumed that the execution of an experiment occurs in the <code>execute</code> method of your experiment class. Of course, you can write Java experiment code outside of that method (using any classes and packages you like), and call it from within <code>runExperiment()</code> --this is Java after all. But what if some of your experiment code is not in Java? LabPal provides some helper classes and objects that allow you to run commands at the command line, as well as read and parse their output. Therefore, you can also use external programs in a LabPal experiment suite.

<p>A simple way of doing so is to create an experiment that inherits from the
<code>CommandExperiment</code> class. An empty experiment looks as follows:</p>

<pre><code>class ProcedureB extends CommandExperiment {

  public ProcedureB() {
    ...
  }
  
  public void createCommand(Parameters input, List&lt;String&gt; command) {
  }
  
  public void readOutput(String output) {
  }
}
</code>
</pre>

<p>The first part of the file is the same boilerplate code as in a regular
experiment. In this case, the work happens in the <code>createCommand()</code> and
<code>readOutput()</code> methods.</p>

<h3>Generating the command to run</h3>

<p>The method <code>createCommand()</code> is responsible for creating the
command-line string that will be called. Since that external call will
probably depend on the experiment's concrete parameters, these parameters are
passed as the argument <code>input</code>; you can read from them as usual.
The resulting command line is written into the <code>command</code> argument;
it is a list of <code>String</code>s, each of which is a part of the
command to run.</p>

<p>Suppose for example that Procedure B takes two numerical parameters, <i>x</i>
and <i>y</i>, and requires to call the external program
<code>myprogram</code>. Suppose also that the value of <i>x</i> must be passed
as is to <code>myprogram</code>, while the value of <i>y</i> must be passed
as the <tt>-a</tt> command-line switch. Method <code>createCommand()</code>
could look like this:</p>

<pre>
<code>  public void createCommand(Parameters input, List&lt;String&gt; command) {
    int x = input.getNumber("x").intValue();
    int y = input.getNumber("y").intValue();
    command.add("myprogram").add(x).add("-a " + y);
  }
</code>
</pre>

<p>The first two lines extract the values of <i>x</i>
and <i>y</i> as before. The last line creates the command line string.
It first adds <code>myprogram</code>, followed by the first argument (the
value of <i>x</i>), followed by the second (the value of <i>y</i> passed as
the command-line argument <code>-a</code>). If <i>x</i>=2 and <i>y</i>=3,
this would result in the following command-line string:</p>

<pre><code>myprogram 2 -a 3
</code></pre>

<h3>Processing the output</h3>

<p>The second part is to do something with the output of the program. To this
end, method <code>readOutput()</code> is called once the command has run. It
contains in argument <code>output</code> the <code>String</code> of what the
command sent to the standard output (if that output contained multiple lines, 
these lines are present in the string). After processing that string
content, you should, as usual, put whatever results of your experiments into the
<code>results</code> parameter map.</p>

<p>Suppose that <code>mycommand</code> prints to the standrd output a single
number, which we will use as the output <i>z</i>. We need to write the following
code:</p>

<pre><code>  public void readOutput(String output, Parameters results) {
    int result = Integer.parseInt(output);
    results.put("z", result);
  }
</code>
</pre>

<p>Of course, it seldom happens that the program's output contains directly the
value we are looking for; more often than not, we need to extract that value
from a more complicated output. For example, <code>mycommand</code> could
output something like this:</p>

<pre><code>The output value of this program is 3. Good bye!
</code></pre>

<p>In such a case, you can use Java's standard regular expression matching to
parse such a string. Method <code>readOutput()</code> could look like:</p>

<pre><code>  public void readOutput(String output, Parameters results) {
    Pattern p = Pattern.compile("The output value of this program is (\\d+)");
    Matcher m = p.matcher(output);
    if (m.find()) {
      results.put("z", Integer.parseInt(m.group(1)));
    }
  }
</code></pre>

<p>This document is by no means a <a                              
href="http://www.vogella.com/tutorials/JavaRegularExpressions/article.html">tutorial
on regular expressions</a>, but the point is to show how you can use whatever
code you wish to break the program's output and make experiment results out of it.</p>

<p>Advantages of extending the <code>CommandExperiment</code> class over running
the commands by yourself are multiple:</p>

<ul>
<li>LabPal takes care of starting that command in its own thread, which can
be stopped at any time using the GUI</li>
<li>LabPal also takes care of watching when the command is finished,
gathering its output in a String object and calling <code>readOutput</code> only
when all this is over</li>
</ul>

<!-- :wrap=soft:mode=markdown: -->