#!/usr/bin/env ruby
# Redmine initialization script for development environment
# This script sets up test data and configuration after Redmine starts

require 'net/http'
require 'json'
require 'uri'

# Wait for Redmine to be ready
def wait_for_redmine(max_attempts = 30)
  attempts = 0
  while attempts < max_attempts
    begin
      uri = URI('http://localhost:3000')
      response = Net::HTTP.get_response(uri)
      return true if response.code == '200'
    rescue
      # Server not ready yet
    end
    attempts += 1
    sleep 2
  end
  false
end

# This script would be executed by a separate init container
# For now, we'll handle initialization manually or via API calls